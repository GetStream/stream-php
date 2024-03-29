<?php

namespace GetStream\Stubs;

use GetStream\Stream\BaseFeed;

class BaseFeedStub extends BaseFeed
{
    public function makeHttpRequest($uri, $method, $data = [], $query_params = [], $resource = '', $action = '')
    {
        return [
            'uri' => $uri,
            'method' => $method,
            'data' => $data,
            'query_params' => $query_params,
        ];
    }
}
