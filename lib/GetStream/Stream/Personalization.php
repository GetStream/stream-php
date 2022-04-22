<?php

namespace GetStream\Stream;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;

class Personalization
{
    const API_ENDPOINT = 'https://personalization.stream-io-api.com/personalization/v1.0/';

    /**
     * @var GuzzleClient
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
            'handler' => Util::handlerStack($apiKey, $apiSecret, 'personalization'),
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
}
