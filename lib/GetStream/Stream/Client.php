<?php
namespace GetStream\Stream;

use DateTime;
use Exception;

class Client implements ClientInterface
{
    const API_ENDPOINT = 'stream-io-api.com/api';

    /**
     * @var string
     */
    protected $api_key;

    /**
     * @var string
     */
    protected $api_secret;

    /**
     * @var string
     */
    protected $location;

    /**
     * @var string
     */
    protected $protocol;

    /**
     * @var Signer
     */
    public $signer;

    /**
     * @var string
     */
    public $api_version;

    /**
     * @var float
     */
    public $timeout;

    /**
     * @param string $api_key
     * @param string $api_secret
     * @param string $api_version
     * @param string $location
     * @param float $timeout
     */
    public function __construct($api_key, $api_secret, $api_version='v1.0', $location='', $timeout=3.0)
    {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
        $this->signer = new Signer($api_key, $api_secret);
        $this->api_version = $api_version;
        $this->timeout = $timeout;
        $this->location = $location;
        $this->protocol = 'https';
    }

    /**
     * @param  string|null $url
     * @return Client
     * @throws Exception
     */
    public static function herokuConnect($url = null)
    {
        if ($url === null) {
            $url = getenv('STREAM_URL');
        }

        $parsed_url = parse_url($url);
        '@phan-var array $parsed_url';

        $api_key = $parsed_url['user'];
        $api_secret = $parsed_url['pass'];

        if ($api_key == '' || $api_secret == '') {
            throw new Exception('url malformed');
        }
        $client = new static($api_key, $api_secret);
        $location = explode('stream-io-api.com', $parsed_url['host'])[0];
        $location = str_replace('.', '', $location);
        $client->setLocation($location);
        return $client;
    }

    /**
     * @param  string $protocol
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
    }

    /**
     * @param  string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @param  string $user_id
     * @param  ?array $extra_data
     * @return string
     */
    public function createUserSessionToken($user_id, array $extra_data=null)
    {
        if (is_null($extra_data)) {
            $extra_data = [];
        }
        return $this->createUserToken($user_id, $extra_data);
    }

    /**
     * @param  string $user_id
     * @param  ?array $extra_data
     * @return string
     */
    public function createUserToken($user_id, array $extra_data=null)
    {
        if (is_null($extra_data)) {
            $extra_data = [];
        }
        return $this->signer->jwtUserSessionToken($user_id, $extra_data);
    }

    /**
     * @param  BaseFeedInterface $feed
     * @param  string $resource
     * @param  string $action
     * @return string
     */
    public function createFeedJWTToken($feed, $resource, $action)
    {
        $feedId = "{$feed->getSlug()}{$feed->getUserId()}";
        return $this->signer->jwtScopeToken($feedId, $resource, $action);
    }

    /**
     * @param  string $feed_slug
     * @param  string $user_id
     * @param  string|null $token
     * @return FeedInterface
     */
    public function feed($feed_slug, $user_id, $token = null)
    {
        if (null === $token) {
            $token = $this->signer->signature($feed_slug . $user_id);
        }
        return new Feed($this, $feed_slug, $user_id, $this->api_key, $token);
    }

    /**
     * @return Batcher
     */
    public function batcher()
    {
        return new Batcher($this, $this->signer, $this->api_key);
    }

    /**
     * @return Personalization
     */
    public function personalization()
    {
        return new Personalization($this, $this->api_key, $this->api_secret);
    }

    /**
     * @return Collections
     */
    public function collections()
    {
        return new Collections($this, $this->api_key, $this->api_secret);
    }

    /**
     * @return Reactions
     */
    public function reactions()
    {
        return new Reactions($this, $this->api_key, $this->api_secret);
    }

    /**
     * @return Users
     */
    public function users()
    {
        return new Users($this, $this->api_key, $this->api_secret);
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        $baseUrl = getenv('STREAM_BASE_URL');
        if (!$baseUrl) {
            $api_endpoint = static::API_ENDPOINT;
            $localPort = getenv('STREAM_LOCAL_API_PORT');
            if ($localPort) {
                $baseUrl = "http://localhost:$localPort/api";
            } else {
                if ($this->location) {
                    $subdomain = "{$this->location}-api";
                } else {
                    $subdomain = 'api';
                }
                $baseUrl = "{$this->protocol}://{$subdomain}." . $api_endpoint;
            }
        }
        return $baseUrl;
    }

    /**
     * @param  string $uri
     * @return string
     */
    public function buildRequestUrl($uri)
    {
        $baseUrl = $this->getBaseUrl();
        return "{$baseUrl}/{$this->api_version}/{$uri}";
    }

    public function getActivities($ids=null, $foreign_id_times=null, $enrich=false, $reactions = null)
    {
        if ($ids!==null) {
            $query_params = ["ids" => join(',', $ids)];
        } else {
            $fids = [];
            $times = [];
            foreach ($foreign_id_times as $fit) {
                $fids[] = $fit[0];
                try {
                    $times[] = $fit[1]->format(DateTime::ISO8601);
                } catch (Exception $e) {
                    // assume it's in the right format already
                    $times[] = $fit[1];
                }
            }
            $query_params = [
                "foreign_ids" => join(',', $fids),
                "timestamps" => join(',', $times)
            ];
        }

        if ($reactions !== null) {
            if (!is_array($reactions)) {
                throw new StreamFeedException("reactions argument should be an associative array");
            }
            if (isset($reactions["own"]) && $reactions["own"]) {
                $query_params["withOwnReactions"] = true;
                if (isset($reactions["user_id"]) && $reactions["user_id"]) {
                    $query_params["user_id"] = $reactions["user_id"];
                }
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

        $token = $this->signer->jwtScopeToken('*', 'activities', '*');
        $activities = new Activities($this, $this->api_key, $token);
        return $activities->_getActivities($query_params, $enrich);
    }

    public function batchPartialActivityUpdate($data)
    {
        if (count($data) > 100) {
            throw new Exception("Max 100 activities allowed in batch update");
        }
        $token = $this->signer->jwtScopeToken('*', 'activities', '*');
        $activityUpdateOp = new ActivitiesOperation($this, $this->api_key, $token);
        return $activityUpdateOp->partiallyUpdateActivity(["changes" => $data]);
    }

    public function doPartialActivityUpdate($id=null, $foreign_id=null, $time=null, $set=null, $unset=null)
    {
        $token = $this->signer->jwtScopeToken('*', 'activities', '*');
        if ($id === null && ($foreign_id === null || $time === null)) {
            throw new Exception(
                "The id or foreign_id+time parameters must be provided and not be None"
            );
        }
        if ($id !== null && ($foreign_id !== null || $time !== null)) {
            throw new Exception(
                "Only one of the id or the foreign_id+time parameters can be provided"
            );
        }

        $data = ["set" => $set, "unset" => $unset];

        if ($id !== null) {
            $data["id"] = $id;
        } else {
            $data["foreign_id"] = $foreign_id;
            $data["time"] = $time;
        }
        $activityUpdateOp = new ActivitiesOperation($this, $this->api_key, $token);
        return $activityUpdateOp->partiallyUpdateActivity($data);
    }

    public function updateActivities($activities)
    {
        if (empty($activities)) {
            return;
        }
        $token = $this->signer->jwtScopeToken('*', 'activities', '*');
        $activityUpdateOp = new ActivitiesOperation($this, $this->api_key, $token);
        return $activityUpdateOp->updateActivities($activities);
    }

    public function updateActivity($activity)
    {
        return $this->updateActivities([$activity]);
    }

    private function getAppActivities($data)
    {
        $token = $this->signer->jwtScopeToken('*', 'activities', '*');
        $op = new ActivitiesOperation($this, $this->api_key, $token);
        return $op->getAppActivities($data);
    }

    /**
     * Retrieves activities for the current app having the given IDs.
     * @param array $ids
     * @return mixed
     */
    public function getActivitiesById($ids = [])
    {
        return $this->getAppActivities(['ids' => $ids]);
    }

    /**
     * Retrieves activities for the current app having the given list of [foreign ID, time] elements.
     * @param array $foreignIdTimes
     * @return mixed
     */
    public function getActivitiesByForeignId($foreignIdTimes = [])
    {
        $foreignIds = [];
        $timestamps = [];
        foreach ($foreignIdTimes as $fidTime) {
            if (!is_array($fidTime) || count($fidTime) != 2) {
                throw new StreamFeedException('malformed foreign ID and time combination');
            }
            array_push($foreignIds, $fidTime[0]);
            array_push($timestamps, $fidTime[1]);
        }
        return $this->getAppActivities(['foreign_ids' => $foreignIds, 'timestamps' => $timestamps]);
    }

    private function activityPartialUpdate($data = [])
    {
        $token = $this->signer->jwtScopeToken('*', 'activities', '*');
        $op = new ActivitiesOperation($this, $this->api_key, $token);
        return $op->activityPartialUpdate($data);
    }

    /**
     * Performs an activity partial update by the given activity ID.
     * @param string $id
     * @param mixed $set
     * @param array $unset
     * @return mixed
     */
    public function activityPartialUpdateById($id, $set = [], $unset = [])
    {
        return $this->activityPartialUpdate(['id' => $id, 'set' => $set, 'unset' => $unset]);
    }

    /**
     * Performs an activity partial update by the given foreign ID and time.
     * @param string $foreign_id
     * @param DateTime|int $time
     * @param mixed $set
     * @param array $unset
     * @return mixed
     */
    public function activityPartialUpdateByForeignId($foreign_id, $time, $set = [], $unset = [])
    {
        return $this->activityPartialUpdate(
            [
                'foreign_id' => $foreign_id,
                'time' => $time,
                'set' => $set,
                'unset' => $unset
            ]
        );
    }

    /**
     * Creates a redirect url for tracking the given events in the context of
     * getstream.io/personalization
     * @param  string $targetUrl
     * @param  array $events
     * @return string
     */
    public function createRedirectUrl($targetUrl, $events)
    {
        $token = $this->signer->jwtScopeToken('*', 'redirect_and_track', '*');
        $analytics = new Analytics($this, $this->api_key, $token);
        return $analytics->createRedirectUrl($targetUrl, $events);
    }
}
