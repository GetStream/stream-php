<?php

namespace GetStream\Stream;

class Activities extends Feed
{
    /**
     * @var string
     */
    protected $token;

    /**
     * @param ClientInterface $client
     * @param string $api_key
     * @param string $token
     */
    public function __construct(ClientInterface $client, $api_key, $token)
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

    public function _getActivities($query_params, $enrich = false)
    {
        if (empty($query_params)) {
            return;
        }

        $url = 'activities/';

        if ($enrich) {
            $url = "enrich/$url";
        }

        return $this->makeHttpRequest($url, 'GET', null, $query_params);
    }
}
