<?php
namespace GetStream\Stream;

use Exception;
use HttpSignatures\Context;

const VERSION = '2.5.2';

class Client
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
     * @param  BaseFeed $feed
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
     * @return Feed
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
        $context = new Context([
            'keys' => array($this->api_key => $this->api_secret),
            'algorithm' => 'hmac-sha256',
            'headers' => array('(request-target)', 'Date'),
        ]);

        return new Batcher($this, $context, $this->api_key);
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

    public function updateActivities($activities)
    {
        if (empty($activities)) {
            return;
        }

        $token = $this->signer->jwtScopeToken('*', 'activities', '*');
        $activityUpdateOp = new ActivityUpdateOperation($this, $this->api_key, $token);
        return $activityUpdateOp->updateActivities($activities);
    }

    public function updateActivity($activity)
    {
        return $this->updateActivities([$activity]);
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
