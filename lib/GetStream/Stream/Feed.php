<?php
namespace GetStream\Stream;

use Exception;
use GuzzleHttp;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Stream\Stream;

class Feed extends BaseFeed
{
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var array
     */
    protected $guzzleOptions = [];

    /**
     * @var array
     */
    protected $httpRequestHeaders = [];

    /**
     * @return \GuzzleHttp\Client
     */
    public static function getHttpClient()
    {
        return new GuzzleHttp\Client();
    }

    public function setGuzzleDefaultOption($option, $value)
    {
        $this->guzzleOptions[$option] = $value;
    }

    /**
     * @param  string $resource
     * @param  string $action
     * @return array
     */
    protected function getHttpRequestHeaders($resource, $action)
    {
        $token = $this->client->createFeedJWTToken($this, $resource, $action);
        return [
            'Authorization'     => $token,
            'Content-Type'      => 'application/json',
            'stream-auth-type'  => 'jwt'
        ];
    }

    /**
     * @param  string $uri
     * @param  string $method
     * @param  array $data
     * @param  array $query_params
     * @param  string $resource
     * @param  string $action
     * @return mixed
     * @throws StreamFeedException
     */
    public function makeHttpRequest($uri, $method, $data = [], $query_params = [], $resource = '', $action = '')
    {
        $query_params['api_key'] = $this->api_key;

        $client = static::getHttpClient();

        foreach ($this->guzzleOptions as $key => $value) {
            $client->setDefaultOption($key, $value);
        }
        $request = $client->createRequest($method, $this->client->buildRequestUrl($uri), ['timeout' => $this->client->timeout]);
        $request->setHeaders($this->getHttpRequestHeaders($resource, $action));

        $query = $request->getQuery();
        foreach ($query_params as $key => $value) {
            $query[$key] = $value;
        }

        if ($method === 'POST' || $method === 'POST') {
            $json_data = json_encode($data);
            $request->setBody(Stream::factory($json_data));
        }

        try {
            $response = $client->send($request);
        } catch (Exception $e) {
            if ($e instanceof ClientException) {
                throw new StreamFeedException($e->getResponse()->getBody());
            } else {
                throw $e;
            }
        }
        return $response->json();
    }
}
