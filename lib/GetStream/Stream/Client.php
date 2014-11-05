<?php
namespace GetStream\Stream;

use Exception;

class Client
{
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
    protected $api_endpoint;

    /**
     * @var Signer
     */
    public $signer;

    /**
     * @param string $api_key
     * @param string $api_secret
     */
    public function __construct($api_key, $api_secret)
    {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
        $this->signer = new Signer($api_secret);
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

        return new static($api_key, $api_secret);
    }

    /**
     * @param  string $feed
     * @return string
     * @throws Exception
     */
    public static function validateFeed($feed)
    {
        $valid_feed_types = ['user', 'flat', 'aggregated', 'notification'];
        $pattern = '/^(' . implode('|', $valid_feed_types) . ')\:([a-z\d]++)$/';
        $pattern = '/^([a-z_]+)\:([a-z\d]+)$/';
        $replace = '\\1\\2';

        $str = preg_replace($pattern, $replace, $feed);
        if (is_null($str) || $str == $feed) {
            throw new Exception('feed must be in format type:id');
        }

        return $str;
    }

    /**
     * @param  string $feed
     * @return string
     */
    public function createToken($feed)
    {
        return $this->signer->signature($feed);
    }

    /**
     * @param  string $feed
     * @param  string|null $token
     * @return Feed
     */
    public function feed($feed, $token = null)
    {
        $feed_auth_name = self::validateFeed($feed);

        if (null === $token) {
            $token = $this->createToken($feed_auth_name);
        }

        return new Feed($this, $feed, $this->api_key, $token);
    }
}
