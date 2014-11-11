<?php

namespace GetStream\Stream;

use GuzzleHttp\Message\Response;
use GuzzleHttp\Subscriber\History;
use GuzzleHttp\Subscriber\Mock;


class _Feed extends Feed
{
    public static $history;

    public static function getHistory()
    {
        if (is_null(_Feed::$history)) {
            _Feed::$history = new History();
        }
        return _Feed::$history;
    }

    public static function getHttpClient()
    {
        $client = parent::getHttpClient();
        $mock = new Mock([
            new Response(200, ['X-Foo' => 'Bar']),
            "HTTP/1.1 202 OK\r\nContent-Length: 0\r\n\r\n"
        ]);
        $client->getEmitter()->attach(_Feed::getHistory());
        $client->getEmitter()->attach($mock);

        return $client;
    }

    protected function buildRequestUrl($uri)
    {
        return BaseFeed::API_ENDPOINT;
    }

}
