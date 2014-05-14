<?php
namespace GetStream\Stream;


class Signer
{
    public $hashFunction = null;

    public function __construct($key)
    {
        $this->key = $key;
        $this->hashFunction = new HMAC;
    }

    public function urlSafeB64encode($value)
    {
        return trim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    public function signature($value)
    {
        $digest = $this->hashFunction->digest($value, $this->key);
        return $this->urlSafeB64encode($digest);
    }
}
