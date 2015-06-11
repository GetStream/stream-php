<?php
namespace GetStream\Stream;

use Exception;

class Client
{
    const API_ENDPOINT = 'getstream.io/api';

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
     * @param string $timeout
     */
    public function __construct($api_key, $api_secret, $api_version='v1.0', $location='', $timeout=3.0)
    {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
        $this->signer = new Signer($api_secret);
        $this->api_version = $api_version;
        $this->location = $location;
        $this->timeout = $timeout;
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
        $location = explode('getstream.io', $parsed_url['host'])[0];
        $location = str_replace('.', '', $location);
        $client->setLocation($location);
        return $client;
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
     * @param  string $uri
     * @return string
     */
    public function buildRequestUrl($uri)
    {
        if (getenv('LOCAL')) {
            $baseUrl = 'http://localhost:8000/api';
        } else {
            if ($this->location) {
                $subdomain = "{$this->location}-api";
            } else {
                $subdomain = 'api';
            }
            $baseUrl = "https://{$subdomain}." . static::API_ENDPOINT;
        }
        return "{$baseUrl}/{$this->api_version}/{$uri}";
    }
}
