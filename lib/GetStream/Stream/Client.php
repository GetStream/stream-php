<?php
namespace GetStream\Stream;

class Client
{
    protected $api_key;
    protected $api_secret;
    protected $api_endpoint;
    public $signer;

    public function __construct($api_key, $api_secret)
    {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
        $this->signer = new Signer($api_secret);
    }

    public static function herokuConnect($url=null)
    {
        if ($url === null) {
            $url = getenv('STREAM_URL');
        }
        $parsed_url = parse_url($url);
        $api_key = $parsed_url['user'];
        $api_secret = $parsed_url['pass'];
        $params = array();
        parse_str($parsed_url['query'], $params);
        $site_id = $params['site'];
        if($api_key == '' || $api_secret == '') {
            throw new Exception("url malformed");
        }
        return new static($api_key, $api_secret);
    }

    public static function validateFeed($feed)
    {
        if (count(explode(':', $feed)) != 2) {
            throw new \Exception("feed must be in format type:id");
        }
        return implode(explode(':', $feed));
    }

    public function createToken($feed)
    {
        return $this->signer->signature($feed);
    }

    public function feed($feed, $token = null)
    {
        $feed_auth_name = $this::validateFeed($feed);
        $token = is_null($token) ? $this->createToken($feed_auth_name) : $token;
        return new Feed($this, $feed, $this->api_key, $token);
    }
}
