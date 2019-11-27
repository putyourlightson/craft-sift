<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sift;

use Craft;
use craft\base\Field;
use craft\base\Plugin;
use craft\elements\Category;
use craft\elements\db\CategoryQuery;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use craft\elements\db\EntryQuery;
use craft\events\CancelableEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\fields\Categories;
use craft\helpers\ElementHelper;
use craft\services\Fields;
use putyourlightson\sift\fields\ReadonlyCategories;
use putyourlightson\sift\models\SettingsModel;
use yii\base\Event;

/**
 * Sift plugin
 *
 * @property SettingsModel $settings
 */
class Sift extends Plugin
{
    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        // Register custom fieldtype
        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = ReadonlyCategories::class;
            }
        );

        // Set up handlers for CP requests only
        if (!Craft::$app->getRequest()->getIsCpRequest()) {
            return;
        }

        $user = Craft::$app->getUser()->getIdentity();

        if ($user === null) {
            return;
        }

        $categoryFieldHandle = $this->settings->categoryFieldHandle;

        if ($categoryFieldHandle === null) {
            return;
        }

        /** @var Categories $categoryField */
        $categoryField = Craft::$app->getFields()->getFieldByHandle($categoryFieldHandle);

        if ($categoryField === null) {
            return;
        }

        /** @var Category[] $categories */
        $categories = $user->$categoryFieldHandle->all();

        Event::on(EntryQuery::class, EntryQuery::EVENT_BEFORE_PREPARE,
            function(CancelableEvent $event) use ($categories) {
                /** @var ElementQuery $query */
                $query = $event->sender;

                $query->relatedTo($categories);
            }
        );

        Event::on(CategoryQuery::class, CategoryQuery::EVENT_BEFORE_PREPARE,
            function(CancelableEvent $event) use ($categories, $categoryField) {
                /** @var CategoryQuery $query */
                $query = $event->sender;

                // Get category group ID from the field source
                $source = ElementHelper::findSource(Category::class, $categoryField->source, 'field');
                $groupId = $source['criteria']['groupId'] ?? null;

                // If the category group ID is not the same as that of the field source
                if ($query->groupId != $groupId) {
                    //$query->relatedTo($categories);
                }
            }
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): SettingsModel
    {
        return new SettingsModel();
    }
}
