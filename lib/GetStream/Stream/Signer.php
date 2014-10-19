<?php
namespace GetStream\Stream;

class Signer
{
    /**
     * @var HMAC
     */
    public $hashFunction;

    /**
     * @param string $key
     */
    public function __construct($key)
    {
        $this->key = $key;
        $this->hashFunction = new HMAC;
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
        $digest = $this->hashFunction->digest($value, $this->key);
        return $this->urlSafeB64encode($digest);
    }
}
