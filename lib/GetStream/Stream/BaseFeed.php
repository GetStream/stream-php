<?php
namespace GetStream\Stream;

class BaseFeed
{

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var string
     */
    protected $user_id;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $base_feed_url;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $api_key;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @param Client $client
     * @param string $feed_slug
     * @param string $user_id
     * @param string $api_key
     * @param string $token
     *
     * @throws StreamFeedException
     */
    public function __construct($client, $feed_slug, $user_id, $api_key, $token)
    {
        if (!$this->validFeedSlug($feed_slug)) {
            throw new StreamFeedException('feed_slug can only contain alphanumeric characters or underscores');
        }

        if (!$this->validUserId($user_id)) {
            throw new StreamFeedException('user_id can only contain alphanumeric characters, underscores or dashes');
        }

        $this->slug = $feed_slug;
        $this->user_id = $user_id;
        $this->id = "$feed_slug:$user_id";
        $this->base_feed_url = "feed/{$feed_slug}/{$user_id}";

        $this->token   = $token;
        $this->api_key = $api_key;

        if ($client instanceof Client) {
            $this->client  = $client;
        }
    }

    /**
     * @param $feed_slug
     *
     * @return bool
     */
    public function validFeedSlug($feed_slug)
    {
        return (preg_match('/^\w+$/', $feed_slug) === 1);
    }

    /**
     * @param $user_id
     *
     * @return bool
     */
    public function validUserId($user_id)
    {
        return (preg_match('/^[-\w]+$/', $user_id) === 1);
    }

    /**
     * @return string
     */
    public function getReadonlyToken()
    {
        return $this->client->createFeedJWTToken($this, '*', 'read');
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param  array $to
     * @return array
     */
    public function signToField($to)
    {
        $recipients = [];
        foreach ($to as $recipient) {
            $bits = explode(':', $recipient);
            $recipient_feed = $this->client->feed($bits[0], $bits[1]);
            $recipient_token = $recipient_feed->getToken();
            $recipients[] = "$recipient $recipient_token";
        }
        return $recipients;
    }

    /**
     * @param  array $activity_data
     * @return mixed
     */
    public function addActivity($activity_data)
    {
        if (array_key_exists('to', $activity_data)) {
            $activity_data['to'] = $this->signToField($activity_data['to']);
        }
        return $this->makeHttpRequest("{$this->base_feed_url}/", 'POST', $activity_data, null, 'feed', 'write');
    }

    /**
     * @param  array $activities_data
     * @return mixed
     */
    public function addActivities($activities_data)
    {
        foreach ($activities_data as $i => $activity) {
            if (array_key_exists('to', $activity)) {
                $activities_data[$i]['to'] = $this->signToField($activity['to']);
            }
        }
        $data = ['activities' => $activities_data];
        return $this->makeHttpRequest("{$this->base_feed_url}/", 'POST', $data, null, 'feed', 'write');
    }

    /**
     * @param  int $activity_id
     * @param  bool $foreign_id
     * @return mixed
     */
    public function removeActivity($activity_id, $foreign_id = false)
    {
        $query_params = [];
        if ($foreign_id === true) {
            $query_params['foreign_id'] = 1;
        }
        return $this->makeHttpRequest("{$this->base_feed_url}/{$activity_id}/", 'DELETE', null, $query_params, 'feed', 'delete');
    }

    /**
     * @param  int $offset
     * @param  int $limit
     * @param  array $options
     * @return mixed
     */
    public function getActivities($offset = 0, $limit = 20, $options = [])
    {
        $query_params = ['offset' => $offset, 'limit' => $limit];
        if (array_key_exists('mark_read', $options) && is_array($options['mark_read'])) {
            $options['mark_read'] = implode(',', $options['mark_read']);
        }
        if (array_key_exists('mark_seen', $options) && is_array($options['mark_seen'])) {
            $options['mark_seen'] = implode(',', $options['mark_seen']);
        }
        $query_params = array_merge($query_params, $options);

        return $this->makeHttpRequest("{$this->base_feed_url}/", 'GET', null, $query_params, 'feed', 'read');
    }

    /**
     * @param string $targetFeedSlug
     * @param string $targetUserId
     * @param int $activityCopyLimit
     *
     * @return mixed
     */
    public function follow($targetFeedSlug, $targetUserId, $activityCopyLimit = 300)
    {
        $data = [
            'target' => "$targetFeedSlug:$targetUserId",
            'activity_copy_limit' => $activityCopyLimit
        ];
        if (null !== $this->client) {
            $target_feed = $this->client->feed($targetFeedSlug, $targetUserId);
            $data['target_token'] = $target_feed->getToken();
        }

        return $this->makeHttpRequest("{$this->base_feed_url}/follows/", 'POST', $data, null, 'follower', 'write');
    }

    /**
     * @deprecated Will be removed in version 3.0.0
     *
     * @param string $targetFeedSlug
     * @param string $targetUserId
     * @param int $activityCopyLimit
     *
     * @return mixed
     */
    public function followFeed($targetFeedSlug, $targetUserId, $activityCopyLimit = 300)
    {
        return $this->follow($targetFeedSlug, $targetUserId, $activityCopyLimit);
    }

    /**
     * @param  int $offset
     * @param  int $limit
     * @return mixed
     */
    public function followers($offset = 0, $limit = 25)
    {
        $query_params = [
            'limit'  => $limit,
            'offset' => $offset,
        ];

        return $this->makeHttpRequest("{$this->base_feed_url}/followers/", 'GET', null, $query_params, 'follower', 'read');
    }

    /**
     * @param  int $offset
     * @param  int $limit
     * @param  array $filter
     * @return mixed
     */
    public function following($offset = 0, $limit = 25, $filter = [])
    {
        $query_params = [
            'limit'  => $limit,
            'offset' => $offset,
            'filter' => implode(',', $filter),
        ];
        return $this->makeHttpRequest("{$this->base_feed_url}/follows/", 'GET', null, $query_params, 'follower', 'read');
    }

    /**
     * @param string $targetFeedSlug
     * @param string $targetUserId
     * @param bool $keepHistory
     *
     * @return mixed
     */
    public function unfollow($targetFeedSlug, $targetUserId, $keepHistory = false)
    {
        $queryParams = [];
        if ($keepHistory) {
            $queryParams['keep_history'] = 'true';
        }
        $targetFeedId = "$targetFeedSlug:$targetUserId";
        return $this->makeHttpRequest("{$this->base_feed_url}/follows/{$targetFeedId}/", 'DELETE', null, $queryParams, 'follower', 'delete');
    }

    /**
     * @deprecated Will be removed in version 3.0.0
     *
     * @param string $targetFeedSlug
     * @param string $targetUserId
     * @param bool $keepHistory
     *
     * @return mixed
     */
    public function unfollowFeed($targetFeedSlug, $targetUserId, $keepHistory = false)
    {
        return $this->unfollow($targetFeedSlug, $targetUserId, $keepHistory);
    }

    /**
     * @deprecated Will be removed in version 3.0.0
     *
     * No need to clean up, one should just use different feed ids.
     *
     * @return mixed
     */
    public function delete()
    {
        return $this->makeHttpRequest("{$this->base_feed_url}/", 'DELETE', null, null, 'feed', 'delete');
    }

    /**
     * @param  string $foreign_id
     * @param  string $time
     * @param  array $new_targets
     * @param  array $added_targets
     * @param  array $removed_targets
     * @return mixed
     */
    public function updateActivityToTargets($foreign_id, $time, $new_targets = [], $added_targets = [], $removed_targets = [])
    {
        $data = [
            'foreign_id' => $foreign_id,
            'time'       => $time,
        ];

        if ($new_targets) {
            $data['new_targets'] = $new_targets;
        }
        
        if ($added_targets) {
            $data['added_targets'] = $added_targets;           
        }
        
        if ($removed_targets) {
            $data['removed_targets'] = $removed_targets;           
        }
        return $this->makeHttpRequest("feed_targets/{$this->slug}/{$this->user_id}/activity_to_targets/", 'POST', $data, null, 'feed_targets', 'write');
    }
}
