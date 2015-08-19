<?php
namespace GetStream\Stream;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;

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
            'handler' => $handler
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
        $client = $this->getHttpClient();
        $headers = $this->getHttpRequestHeaders($resource, $action);

        $Uri = new Psr7\Uri($this->client->buildRequestUrl($uri));
        $Uri = $Uri->withQuery(http_build_query($query_params));

        $request = new Request(
            $method, $Uri, $headers, null, $this->guzzleOptions
        );

        if ($method === 'POST' || $method === 'POST') {
            $json_data = json_encode($data);
            $request = $request->withBody(Psr7\stream_for($json_data));
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
        $body = $response->getBody()->getContents();
        $json_body = json_decode($body, true);
        return $json_body;
    }
}
