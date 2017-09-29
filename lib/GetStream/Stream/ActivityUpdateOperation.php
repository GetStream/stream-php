<?php
namespace GetStream\Stream;

use Exception;
use GuzzleHttp;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;


class ActivityUpdateOperation extends Feed
{

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

    public function updateActivities($activities)
    {
        if (empty($activities)) {
            return;
        }

        $data = [
            'activities' => $activities
        ];
        return $this->makeHttpRequest('activities/', 'POST', $data);
    }

}
