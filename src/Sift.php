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
use craft\elements\User;
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

        // Ensure CP request
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            return;
        }

        $user = Craft::$app->getUser()->getIdentity();

        //  Ensure user is logged in and not an admin
        if ($user === null || $user->admin) {
            return;
        }

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

    /**
     * @param Event $event
     */
    public function handleEntryQuery(Event $event)
    {
        /** @var EntryQuery $query */
        $query = $event->sender;

        // Ensure the query is not limited to 1
        if ($query->limit == 1) {
            return;
        }

        $user = Craft::$app->getUser()->getIdentity();

        $relatedTo = ['or'];

        foreach ($this->settings->entryFieldHandles as $entryFieldHandle => $userFieldHandle) {
            $userCategoryIds = $user->{$userFieldHandle}->ids();

            if (!empty($userCategoryIds)) {
                $relatedTo[] = [
                    'field' => $entryFieldHandle,
                    'targetElement' => $userCategoryIds,
                ];
            }
        }

        $query->relatedTo($relatedTo);
    }

    /**
     * @param Event $event
     */
    public function handleCategoryQuery(Event $event)
    {
        /** @var CategoryQuery $query */
        $query = $event->sender;

        $user = Craft::$app->getUser()->getIdentity();

        $query->relatedTo(['sourceElement' => $user]);
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
