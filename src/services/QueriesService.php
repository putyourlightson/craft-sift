<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sift\services;

use Craft;
use craft\base\Component;
use craft\elements\db\CategoryQuery;
use craft\elements\db\EntryQuery;
use craft\elements\Entry;
use craft\events\CancelableEvent;
use putyourlightson\sift\Sift;
use verbb\workflow\elements\db\SubmissionQuery;

class QueriesService extends Component
{
    /**
     * @param CancelableEvent $event
     */
    public function handleEntryQuery(CancelableEvent $event)
    {
        /** @var EntryQuery $query */
        $query = $event->sender;

        // Ensure the query is not limited to 1
        if ($query->limit == 1) {
            return;
        }

        $this->_applyEntryQueryRelation($query);
    }

    /**
     * @param CancelableEvent $event
     */
    public function handleCategoryQuery(CancelableEvent $event)
    {
        /** @var CategoryQuery $query */
        $query = $event->sender;

        $user = Craft::$app->getUser()->getIdentity();

        $query->relatedTo(['sourceElement' => $user]);
    }

    /**
     * @param CancelableEvent $event
     */
    public function handleSubmissionQuery(CancelableEvent $event)
    {
        /** @var SubmissionQuery $query */
        $query = $event->sender;

        $entryQuery = Entry::find()
            ->drafts()
            ->status(null);

        $this->_applyEntryQueryRelation($entryQuery);

        $entryIds = $entryQuery->ids();

        $query->andWhere(['ownerId' => $entryIds]);
    }

    /**
     * @param EntryQuery $query
     */
    private function _applyEntryQueryRelation(EntryQuery $query)
    {
        $user = Craft::$app->getUser()->getIdentity();

        $relatedTo = ['or'];

        foreach (Sift::$plugin->settings->entryFieldHandles as $entryFieldHandle => $userFieldHandle) {
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
}
