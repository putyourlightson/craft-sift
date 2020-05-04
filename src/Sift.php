<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sift;

use Craft;
use craft\base\Plugin;
use craft\elements\db\CategoryQuery;
use craft\elements\db\EntryQuery;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Fields;
use putyourlightson\sift\fields\ReadonlyCategories;
use putyourlightson\sift\models\SettingsModel;
use putyourlightson\sift\services\QueriesService;
use verbb\workflow\elements\db\SubmissionQuery;
use yii\base\Event;

/**
 * Sift plugin
 *
 * @property QueriesService $queries
 * @property SettingsModel $settings
 */
class Sift extends Plugin
{
    /**
     * @var Sift $plugin
     */
    static public $plugin;

    public function init()
    {
        parent::init();

        self::$plugin = $this;

        $this->_registerComponents();
        $this->_registerFieldTypes();
        $this->_registerEvents();
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): SettingsModel
    {
        return new SettingsModel();
    }

    /**
     * Registers the components.
     */
    private function _registerComponents()
    {
        $this->setComponents([
            'queries' => QueriesService::class,
        ]);
    }

    /**
     * Registers the field types.
     */
    private function _registerFieldTypes()
    {
        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = ReadonlyCategories::class;
            }
        );
    }

    /**
     * Registers the events.
     */
    private function _registerEvents()
    {
        $user = Craft::$app->getUser()->getIdentity();

        //  Ensure user is logged in and not an admin
        if ($user === null || $user->admin) {
            return;
        }

        // Handle entry queries
        Event::on(EntryQuery::class,
            EntryQuery::EVENT_BEFORE_PREPARE,
            [$this->queries, 'handleEntryQuery']
        );

        // Handle category queries
        Event::on(CategoryQuery::class,
            CategoryQuery::EVENT_BEFORE_PREPARE,
            [$this->queries, 'handleCategoryQuery']
        );

        // Handle submission queries
        if (class_exists(SubmissionQuery::class)) {
            Event::on(SubmissionQuery::class,
                SubmissionQuery::EVENT_BEFORE_PREPARE,
                [$this->queries, 'handleSubmissionQuery']
            );
        }
    }
}
