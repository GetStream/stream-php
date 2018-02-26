<?php

namespace GetStream\Stream;

use Firebase\JWT\JWT;

class Signer
{
    /**
     * @var string
     */
    private $api_key;

    /**
     * @var string
     */
    private $api_secret;

    /**
     * @param string $api_key
     * @param string $api_secret
     */
    public function __construct($api_key, $api_secret)
    {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
    }

    /**
     * @param  string $value
     * @return string
     */
    public function signature($value)
    {
        $digest = hash_hmac('sha1', $value, sha1($this->api_secret, true), true);

        return trim(strtr(base64_encode($digest), '+/', '-_'), '=');
    }

    /**
     * @param  string $feedId
     * @param  string $resource
     * @param  string $action
     * @return string
     */
    public function jwtScopeToken($feedId, $resource, $action)
    {
        $payload = [
            'action'   => $action,
            'feed_id'  => $feedId,
            'resource' => $resource,
        ];

        return JWT::encode($payload, $this->api_secret, 'HS256');
    }
}
