<?php

namespace GetStream\Stream;

class GetAppActivitiesOperation extends Feed
{
    /**
     * @var string
     */
    protected $token;

    /**
     * @param Client $client
     * @param string $api_key
     * @param string $token
     */
    public function __construct($client, $api_key, $token)
    {
        $this->client = $client;
        $this->api_key = $api_key;
        $this->token = $token;
    }

    /**
     * @param string $resource
     * @param string $action
     *
     * @return array
     */
    protected function getHttpRequestHeaders($resource, $action)
    {
        $headers = parent::getHttpRequestHeaders($resource, $action);
        $headers['Authorization'] = $this->token;

        return $headers;
    }

    public function getAppActivities($data = [])
    {
        $params = [];
        foreach ($data as $key => $value) {
            $params[$key] = implode(',', $value);
        }

        return $this->makeHttpRequest('activities/', 'GET', [], $params);
    }
}
