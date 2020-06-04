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

        // Don't filter queries that are indexes
        if ($query->indexBy !== null) {
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

        // Ensure the query is not limited to 1
        if ($query->limit == 1) {
            return;
        }

        // Ensure that the submission's owner (entry or draft) fulfills the relations
        $entryQuery = Entry::find()->status(null);
        $this->_applyEntryQueryRelation($entryQuery);
        $entryIds = $entryQuery->ids();

        $draftEntryQuery = Entry::find()->drafts()->status(null);
        $this->_applyEntryQueryRelation($draftEntryQuery);
        $draftEntryIds = $draftEntryQuery->ids();

        $query->andWhere(['ownerId' => array_merge($entryIds, $draftEntryIds)]);
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
