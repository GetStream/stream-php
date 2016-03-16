<?php
namespace GetStream\Stream;

use Exception;
use GuzzleHttp;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;


class Analytics extends Feed
{
    const API_ENDPOINT = 'https://analytics.getstream.io/analytics/';

    protected $token;

    public function __construct($client, $api_key, $token)
    {
        $this->client = $client;
        $this->api_key = $api_key;
        $this->token = $token;
    }

    protected function getHttpRequestHeaders($resource, $action)
    {
        $headers = parent::getHttpRequestHeaders($resource, $action);
        $headers['Authorization'] = $this->token;
        return $headers;
    }

    public function createRedirectUrl($targetUrl, $events)
    {
        $parsed_url = parse_url($targetUrl);
        $query_params = $this->getHttpRequestHeaders($resource, $action);
        $query_params['api_key'] = $this->api_key;
        $query_params['url'] = $targetUrl;
        $query_params['auth_type'] = 'jwt';
        $query_params['authorization'] = $query_params['Authorization'];
        unset($query_params['Authorization']);
        unset($query_params['stream-auth-type']);
        unset($query_params['Content-Type']);
        unset($query_params['X-Stream-Client']);
        $query_params['events'] = json_encode($events);
        $qString = http_build_query($query_params);
        return static::API_ENDPOINT . 'redirect/' . "?{$qString}";
    }

}
