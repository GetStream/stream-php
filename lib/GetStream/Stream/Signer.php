<?php
namespace GetStream\Stream;
use HttpSignatures\Context;

class Signer
{
    /**
     * @var HMAC
     */
    public $hashFunction;

    /**
     * @var string
     */
    private $api_key;

    /**
     * @var string
     */
    private $api_secret;

    /**
     * @var HttpSignatures\Context
     */
    public $context;

    /**
     * @param string $api_key
     * @param string $api_secret
     */
    public function __construct($api_key, $api_secret)
    {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
        $this->hashFunction = new HMAC;
        $this->context = new Context(array(
          'keys' => array($api_key =>$api_secret),
          'algorithm' => 'hmac-sha256',
          'headers' => array('(request-target)', 'Date'),
        ));
    }

    /**
     * @param  string $value
     * @return string
     */
    public function urlSafeB64encode($value)
    {
        return trim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    /**
     * @param  string $value
     * @return string
     */
    public function signature($value)
    {
        $digest = $this->hashFunction->digest($value, $this->api_secret);
        return $this->urlSafeB64encode($digest);
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
            'resource' => $resource
        ];
        return \Firebase\JWT::encode($payload, $this->api_secret, 'HS256');
    }

}
