<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sift;

use Craft;
use craft\base\Plugin;
use craft\elements\Category;
use craft\elements\db\CategoryQuery;
use craft\elements\db\EntryQuery;
use craft\events\RegisterComponentTypesEvent;
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

        // Set up handlers for CP requests with a logged in user only
        if (Craft::$app->getRequest()->getIsCpRequest() && Craft::$app->getUser()->getIdentity()) {
            // Handle entry queries
            Event::on(EntryQuery::class,
                EntryQuery::EVENT_BEFORE_PREPARE,
                [$this, 'handleEntryQuery']
            );

            // Handle category queries
            Event::on(CategoryQuery::class,
                CategoryQuery::EVENT_BEFORE_PREPARE,
                [$this, 'handleCategoryQuery']
            );
        }
    }

    /**
     * @param Event $event
     */
    public function handleEntryQuery(Event $event)
    {
        /** @var EntryQuery $query */
        $query = $event->sender;

        // If the query is not limited to 1
        if ($query->limit != 1) {
            $relatedTo = $this->_getRelatedToCategories($this->settings->entryRelatedToKey);
            $query->relatedTo($relatedTo);
        }
    }

    /**
     * @param Event $event
     */
    public function handleCategoryQuery(Event $event)
    {
        /** @var CategoryQuery $query */
        $query = $event->sender;

        // Get category group IDs to ignore from the category field sources
        $ignoreGroupIds = [];

        foreach ($this->settings->categoryFieldHandles as $categoryFieldHandle) {
            $categoryField = Craft::$app->getFields()->getFieldByHandle($categoryFieldHandle);

            if ($categoryField !== null) {
                $source = ElementHelper::findSource(Category::class, $categoryField->source, 'field');
                $groupId = $source['criteria']['groupId'] ?? null;

                if ($groupId) {
                    $ignoreGroupIds[] = $groupId;
                }
            }
        }

        // If the category group ID is not in the group IDs to ignore
        if (!in_array($query->groupId, $ignoreGroupIds)) {
            $relatedTo = $this->_getRelatedToCategories($this->settings->categoryRelatedToKey);
            $query->relatedTo($relatedTo);
        }
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

    // Private Methods
    // =========================================================================

    /**
     * Returns the `relatedTo` param with the user categories.
     *
     * @param string $relatedToKey
     *
     * @return array
     */
    private function _getRelatedToCategories(string $relatedToKey): array
    {
        $user = Craft::$app->getUser()->getIdentity();

        $relatedTo = ['or'];

        foreach ($this->settings->categoryFieldHandles as $categoryFieldHandle) {
            $userCategories = $user->{$categoryFieldHandle};

            if (!empty($userCategories)) {
                $relatedTo[] = [$relatedToKey => $userCategories];
            }
        }

        return $relatedTo;
    }
}
