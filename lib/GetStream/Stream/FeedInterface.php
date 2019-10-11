<?php

namespace GetStream\Stream;

interface FeedInterface extends BaseFeedInterface
{
    /**
     * @return \GuzzleHttp\HandlerStack
     */
    public function getHandlerStack();

    /**
     * @return \GuzzleHttp\Client
     */
    public function getHttpClient();

    public function setGuzzleDefaultOption($option, $value);

    /**
     * @param string $uri
     * @param string $method
     * @param array $data
     * @param array $query_params
     * @param string $resource
     * @param string $action
     * @return mixed
     * @throws StreamFeedException
     */
    public function makeHttpRequest($uri, $method, $data = [], $query_params = [], $resource = '', $action = '');
}
