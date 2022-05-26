<?php

namespace GetStream\Stream;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Uri;

class BaseFeed implements BaseFeedInterface
{
    /**
    * @var array
    */
    protected $guzzleOptions = [];

    /**
     * @var array
     */
    protected $httpRequestHeaders = [];

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
     * @var ClientInterface
     */
    protected $client;

    /**
     * @param ClientInterface $client
     * @param string $feed_slug
     * @param string $user_id
     * @param string $api_key
     * @param string $token
     *
     * @throws StreamFeedException
     */
    public function __construct(ClientInterface $client, $feed_slug, $user_id, $api_key, $token)
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

        $this->client = $client;
    }

    /**
     * @param string $feed_slug
     *
     * @return bool
     */
    public function validFeedSlug($feed_slug)
    {
        return (preg_match('/^\w+$/', $feed_slug) === 1);
    }

    /**
     * @param string $user_id
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
     * @param array $to
     *
     * @return array
     */
    public function signToField($to)
    {
        return array_map(function ($recipient) {
            $bits = explode(':', $recipient);
            $recipient_feed = $this->client->feed($bits[0], $bits[1]);
            $recipient_token = $recipient_feed->getToken();

            return "$recipient $recipient_token";
        }, $to);
    }

    /**
     * @param array $activity
     * @return mixed
     *
     * @throws StreamFeedException
     */
    public function addActivity($activity)
    {
        if (array_key_exists('to', $activity)) {
            $activity['to'] = $this->signToField($activity['to']);
        }

        return $this->makeHttpRequest("{$this->base_feed_url}/", 'POST', $activity, [], 'feed', 'write');
    }

    /**
     * @param array $activities
     * @return mixed
     *
     * @throws StreamFeedException
     */
    public function addActivities($activities)
    {
        foreach ($activities as &$activity) {
            if (array_key_exists('to', $activity)) {
                $activity['to'] = $this->signToField($activity['to']);
            }
        }

        return $this->makeHttpRequest("{$this->base_feed_url}/", 'POST', compact('activities'), [], 'feed', 'write');
    }

    /**
     * @param string $activity_id
     * @param bool $foreign_id
     * @return mixed
     *
     * @throws StreamFeedException
     */
    public function removeActivity($activity_id, $foreign_id = false)
    {
        $query_params = [];

        if ($foreign_id === true) {
            $query_params['foreign_id'] = 1;
        }

        return $this->makeHttpRequest("{$this->base_feed_url}/{$activity_id}/", 'DELETE', [], $query_params, 'feed', 'delete');
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param array $options
     * @return mixed
     *
     * @throws StreamFeedException
     */
    public function getActivities($offset = 0, $limit = 20, $options = [], $enrich=false, $reactions = null)
    {
        if ($options === null) {
            $options = [];
        }
        $query_params = ['offset' => $offset, 'limit' => $limit];
        if (array_key_exists('mark_read', $options) && is_array($options['mark_read'])) {
            $options['mark_read'] = implode(',', $options['mark_read']);
        }
        if (array_key_exists('mark_seen', $options) && is_array($options['mark_seen'])) {
            $options['mark_seen'] = implode(',', $options['mark_seen']);
        }
        $query_params = array_merge($query_params, $options);

        if ($reactions !== null) {
            if (!is_array($reactions)) {
                throw new StreamFeedException("reactions argument should be an associative array");
            }
            if (isset($reactions["own"]) && $reactions["own"]) {
                $query_params["withOwnReactions"] = true;
                $enrich = true;
            }
            if (isset($reactions["recent"]) && $reactions["recent"]) {
                $query_params["withRecentReactions"] = true;
                $enrich = true;
            }
            if (isset($reactions["counts"]) && $reactions["counts"]) {
                $query_params["withReactionCounts"] = true;
                $enrich = true;
            }
            if (isset($reactions["kinds"]) && $reactions["kinds"]) {
                $query_params["reactionKindsFilter"] = implode(",", $reactions["kinds"]);
                $enrich = true;
            }
        }

        $prefix_enrich = $enrich ? 'enrich/' : '';

        return $this->makeHttpRequest("{$prefix_enrich}{$this->base_feed_url}/", 'GET', [], $query_params, 'feed', 'read');
    }

    /**
     * @param string $targetFeedSlug
     * @param string $targetUserId
     * @param int $activityCopyLimit
     *
     * @return mixed
     *
     * @throws StreamFeedException
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

        return $this->makeHttpRequest("{$this->base_feed_url}/follows/", 'POST', $data, [], 'follower', 'write');
    }

    /**
     * @param  int $offset
     * @param  int $limit
     * @return mixed
     *
     * @throws StreamFeedException
     */
    public function followers($offset = 0, $limit = 25)
    {
        $query_params = [
            'limit'  => $limit,
            'offset' => $offset,
        ];

        return $this->makeHttpRequest("{$this->base_feed_url}/followers/", 'GET', [], $query_params, 'follower', 'read');
    }

    /**
     * @param  int $offset
     * @param  int $limit
     * @param  array $filter
     * @return mixed
     *
     * @throws StreamFeedException
     */
    public function following($offset = 0, $limit = 25, $filter = [])
    {
        $query_params = [
            'limit'  => $limit,
            'offset' => $offset,
            'filter' => implode(',', $filter),
        ];
        return $this->makeHttpRequest("{$this->base_feed_url}/follows/", 'GET', [], $query_params, 'follower', 'read');
    }

    /**
     * @param string $targetFeedSlug
     * @param string $targetUserId
     * @param bool $keepHistory
     *
     * @return mixed
     *
     * @throws StreamFeedException
     */
    public function unfollow($targetFeedSlug, $targetUserId, $keepHistory = false)
    {
        $queryParams = [];
        if ($keepHistory) {
            $queryParams['keep_history'] = 'true';
        }
        $targetFeedId = "$targetFeedSlug:$targetUserId";
        return $this->makeHttpRequest("{$this->base_feed_url}/follows/{$targetFeedId}/", 'DELETE', [], $queryParams, 'follower', 'delete');
    }

    /**
     * @param  string $foreign_id
     * @param  string $time
     * @param  array $new_targets
     * @param  array $added_targets
     * @param  array $removed_targets
     * @return mixed
     *
     * @throws StreamFeedException
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
        return $this->makeHttpRequest("feed_targets/{$this->slug}/{$this->user_id}/activity_to_targets/", 'POST', $data, [], 'feed_targets', 'write');
    }

    /**
     * @return \GuzzleHttp\HandlerStack
     */
    public function getHandlerStack()
    {
        return HandlerStack::create();
    }

    /**
     * @return \GuzzleHttp\Client
     */
    public function getHttpClient()
    {
        $handler = $this->client->getCustomHttpHandlerStack();
        if (!$handler) {
            $handler = $this->getHandlerStack();
        }

        return new GuzzleClient([
            'base_uri' => $this->client->getBaseUrl(),
            'timeout' => $this->client->timeout,
            'handler' => $handler,
            'headers' => ['Accept-Encoding' => 'gzip'],
        ]);
    }

    public function setGuzzleDefaultOption($option, $value)
    {
        $this->guzzleOptions[$option] = $value;
    }

    /**
     * @param  string $resource
     * @param  string $action
     * @return array
     */
    protected function getHttpRequestHeaders($resource, $action)
    {
        $token = $this->client->createFeedJWTToken($this, $resource, $action);

        return [
            'Authorization' => $token,
            'Content-Type' => 'application/json',
            'stream-auth-type' => 'jwt',
            'X-Stream-Client' => 'stream-php-client-' . Constant::VERSION,
        ];
    }

    /**
     * @param  string $uri
     * @param  string $method
     * @param  array $data
     * @param  array $query_params
     * @param  string $resource
     * @param  string $action
     * @return mixed
     * @throws StreamFeedException
     */
    public function makeHttpRequest($uri, $method, $data = [], $query_params = [], $resource = '', $action = '')
    {
        $query_params['api_key'] = $this->api_key;
        $client = $this->getHttpClient();
        $headers = $this->getHttpRequestHeaders($resource, $action);

        $uri = (new Uri($this->client->buildRequestUrl($uri)))
            ->withQuery(http_build_query($query_params));

        $options = $this->guzzleOptions;
        $options['headers'] = $headers;

        if ($method === 'POST') {
            $options['json'] = $data;
        }

        try {
            $response = $client->request($method, $uri, $options);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $msg = $response->getBody();
            $code = $response->getStatusCode();
            $previous = $e;
            throw new StreamFeedException($msg, $code, $previous);
        }

        $body = $response->getBody()->getContents();

        return json_decode($body, true);
    }
}
