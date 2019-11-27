<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sift;

use Craft;
use craft\base\Plugin;
use craft\elements\db\CategoryQuery;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use craft\elements\db\EntryQuery;
use craft\events\CancelableEvent;
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

        $user = Craft::$app->getUser()->getIdentity();

        if ($user === null || !Craft::$app->getRequest()->getIsCpRequest()) {
            return;
        }

        if ($this->settings->categoryFieldHandle === null) {
            return;
        }

        $categories = $user->{$this->settings->categoryFieldHandle}->all();

        Event::on(EntryQuery::class, EntryQuery::EVENT_BEFORE_PREPARE,
            function(CancelableEvent $event) use ($categories) {
                /** @var ElementQuery $query */
                $query = $event->sender;
                $this->_filter($query, $categories);
            }
        );

        Event::on(CategoryQuery::class, CategoryQuery::EVENT_BEFORE_PREPARE,
            function(CancelableEvent $event) use ($categories) {
                /** @var ElementQuery $query */
                $query = $event->sender;
                $this->_filter($query, $categories);
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

    // Private Methods
    // =========================================================================

    /**
     * Filters the elements by the provided categories.
     *
     * @param ElementQueryInterface $query
     * @param array $categories
     */
    private function _filter(ElementQueryInterface $query, array $categories)
    {
        $query->relatedTo($categories);
    }
}
