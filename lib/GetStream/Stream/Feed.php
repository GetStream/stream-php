<?php

namespace GetStream\Stream;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Uri;

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
     * @return \GuzzleHttp\HandlerStack
     */
    public function getHandlerStack()
    {
        return HandlerStack::create();
    }

    /**
     * @return \GuzzleHttp\Client
     */
    public function getHttpClient()
    {
        $handler = $this->getHandlerStack();
        return new GuzzleClient([
            'base_uri' => $this->client->getBaseUrl(),
            'timeout' => $this->client->timeout,
            'handler' => $handler,
            'headers' => ['Accept-Encoding' => 'gzip'],
        ]);
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
            'Authorization' => $token,
            'Content-Type' => 'application/json',
            'stream-auth-type' => 'jwt',
            'X-Stream-Client' => 'stream-php-client-' . VERSION,
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
        $client = $this->getHttpClient();
        $headers = $this->getHttpRequestHeaders($resource, $action);

        $uri = (new Uri($this->client->buildRequestUrl($uri)))
            ->withQuery(http_build_query($query_params));

        $options = $this->guzzleOptions;
        $options['headers'] = $headers;

        if ($method === 'POST') {
            $options['json'] = $data;
        }

        try {
            $response = $client->request($method, $uri, $options);
        } catch (ClientException $e) {
            throw new StreamFeedException($e->getResponse()->getBody());
        }

        $body = $response->getBody()->getContents();

        return json_decode($body, true);
    }
}
