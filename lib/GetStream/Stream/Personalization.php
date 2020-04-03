<?php

namespace GetStream\Stream;

use Firebase\JWT\JWT;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;

class Personalization
{
    const API_ENDPOINT = 'https://personalization.stream-io-api.com/personalization/v1.0/';

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $apiSecret;

    /**
     * @param ClientInterface $streamClient
     * @param string $apiKey
     * @param string $apiSecret
     */
    public function __construct(ClientInterface $streamClient, $apiKey, $apiSecret)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->client = new GuzzleClient([
            'base_uri' => self::API_ENDPOINT,
            'timeout' => $streamClient->timeout,
            'handler' => $this->handlerStack(),
        ]);
    }

    /**
     * @param string $resource
     * @param array $params
     *
     * @return array
     */
    public function get($resource, array $params)
    {
        return $this->request('GET', $resource, $params);
    }

    /**
     * @param string $resource
     * @param array $params
     *
     * @return array
     */
    public function post($resource, array $params)
    {
        return $this->request('POST', $resource, $params);
    }

    /**
     * @param string $resource
     * @param array $params
     *
     * @return array
     */
    public function delete($resource, array $params)
    {
        return $this->request('DELETE', $resource, $params);
    }

    /**
     * @param string $method
     * @param string $resource
     * @param array $params
     *
     * @return array
     */
    private function request($method, $resource, array $params)
    {
        $queryParams = ['api_key' => $this->apiKey];
        $queryParams += $params;

        $uri = $resource .'/?'. http_build_query($queryParams);

        try {
            $response = $this->client->request($method, $uri);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $msg = $response->getBody();
            $code = $response->getStatusCode();
            $previous = $e;
            throw new StreamFeedException($msg, $code, $previous);
        }

        $body = $response->getBody()->getContents();

        return json_decode($body, true);
    }

    /**
     * @return HandlerStack
     */
    private function handlerStack()
    {
        $token = JWT::encode([
            'action' => '*',
            'user_id' => '*',
            'feed_id' => '*',
            'resource' => 'personalization',
        ], $this->apiSecret, 'HS256');

        $stack = HandlerStack::create();
        $stack->push(function (callable $handler) use ($token) {
            return function (RequestInterface $request, array $options) use ($handler, $token) {
                $request = $request
                    ->withAddedHeader('Authorization', $token)
                    ->withAddedHeader('Stream-Auth-Type', 'jwt')
                    ->withAddedHeader('Content-Type', 'application/json')
                    ->withAddedHeader('X-Stream-Client', 'stream-php-client-' . Client::VERSION);

                return $handler($request, $options);
            };
        });

        return $stack;
    }
}
