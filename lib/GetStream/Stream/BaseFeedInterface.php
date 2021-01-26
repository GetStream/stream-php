<?php

namespace GetStream\Stream;

interface BaseFeedInterface
{
    /**
     * @param string $feed_slug
     *
     * @return bool
     */
    public function validFeedSlug($feed_slug);

    /**
     * @param string $user_id
     *
     * @return bool
     */
    public function validUserId($user_id);

    /**
     * @return string
     */
    public function getReadonlyToken();

    /**
     * @return string
     */
    public function getToken();

    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getSlug();

    /**
     * @return string
     */
    public function getUserId();

    /**
     * @param array $to
     *
     * @return array
     */
    public function signToField($to);

    /**
     * @param array $activity
     * @return mixed
     *
     * @throws StreamFeedException
     */
    public function addActivity($activity);

    /**
     * @param array $activities
     * @return mixed
     *
     * @throws StreamFeedException
     */
    public function addActivities($activities);

    /**
     * @param string $activity_id
     * @param bool $foreign_id
     * @return mixed
     *
     * @throws StreamFeedException
     */
    public function removeActivity($activity_id, $foreign_id = false);

    /**
     * @param int $offset
     * @param int $limit
     * @param array $options
     * @return mixed
     *
     * @throws StreamFeedException
     */
    public function getActivities($offset = 0, $limit = 20, $options = [], $enrich = false, $reactions = null);

    /**
     * @param string $targetFeedSlug
     * @param string $targetUserId
     * @param int $activityCopyLimit
     *
     * @return mixed
     *
     * @throws StreamFeedException
     */
    public function follow($targetFeedSlug, $targetUserId, $activityCopyLimit = 300);

    /**
     * @param int $offset
     * @param int $limit
     * @return mixed
     *
     * @throws StreamFeedException
     */
    public function followers($offset = 0, $limit = 25);

    /**
     * @param int $offset
     * @param int $limit
     * @param array $filter
     * @return mixed
     *
     * @throws StreamFeedException
     */
    public function following($offset = 0, $limit = 25, $filter = []);

    /**
     * @param string $targetFeedSlug
     * @param string $targetUserId
     * @param bool $keepHistory
     *
     * @return mixed
     *
     * @throws StreamFeedException
     */
    public function unfollow($targetFeedSlug, $targetUserId, $keepHistory = false);

    /**
     * @param string $foreign_id
     * @param string $time
     * @param array $new_targets
     * @param array $added_targets
     * @param array $removed_targets
     * @return mixed
     *
     * @throws StreamFeedException
     */
    public function updateActivityToTargets($foreign_id, $time, $new_targets = [], $added_targets = [], $removed_targets = []);
}
