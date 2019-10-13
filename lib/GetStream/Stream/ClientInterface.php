<?php

namespace GetStream\Stream;

interface ClientInterface
{
    /**
     * @param string $protocol
     */
    public function setProtocol($protocol);

    /**
     * @param string $location
     */
    public function setLocation($location);

    /**
     * @param string $user_id
     * @param array $extra_data
     * @return string
     */
    public function createUserSessionToken($user_id, array $extra_data = null);

    /**
     * @param string $user_id
     * @param array $extra_data
     * @return string
     */
    public function createUserToken($user_id, array $extra_data = null);

    /**
     * @param BaseFeedInterface $feed
     * @param string $resource
     * @param string $action
     * @return string
     */
    public function createFeedJWTToken($feed, $resource, $action);

    /**
     * @param string $feed_slug
     * @param string $user_id
     * @param string|null $token
     * @return FeedInterface
     */
    public function feed($feed_slug, $user_id, $token = null);

    /**
     * @return Batcher
     */
    public function batcher();

    /**
     * @return Personalization
     */
    public function personalization();

    /**
     * @return Collections
     */
    public function collections();

    /**
     * @return Reactions
     */
    public function reactions();

    /**
     * @return Users
     */
    public function users();

    /**
     * @return string
     */
    public function getBaseUrl();

    /**
     * @param string $uri
     * @return string
     */
    public function buildRequestUrl($uri);

    public function getActivities($ids = null, $foreign_id_times = null);

    public function batchPartialActivityUpdate($data);

    public function doPartialActivityUpdate($id = null, $foreign_id = null, $time = null, $set = null, $unset = null);

    public function updateActivities($activities);

    public function updateActivity($activity);

    /**
     * Creates a redirect url for tracking the given events in the context of
     * getstream.io/personalization
     * @param string $targetUrl
     * @param array $events
     * @return string
     */
    public function createRedirectUrl($targetUrl, $events);
}
