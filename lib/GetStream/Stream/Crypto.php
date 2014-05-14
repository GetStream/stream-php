<?php
namespace GetStream\Stream;

class HMAC
{
    public static function digest($data, $key)
    {
        return hash_hmac('sha1', $data, $key);
    }
}
