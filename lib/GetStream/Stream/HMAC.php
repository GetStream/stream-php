<?php
namespace GetStream\Stream;

class HMAC
{
    public static function digest($data, $key)
    {
        return hash_hmac('sha1', $data, sha1($key, true), true);
    }
}
