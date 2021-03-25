<?php

namespace GetStream\Stream;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use Utils;

class Users
{
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
            'base_uri' => $streamClient->getBaseUrl().'/'.$streamClient->api_version.'/',
            'timeout' => $streamClient->timeout,
            'handler' => Util::handlerStack($apiKey, $apiSecret, 'users'),
        ]);
    }

    private function doRequest($method, $endpoint, $params=null)
    {
        if ($params === null) {
            $params = [];
        }
        if ($method === 'POST' || $method === 'PUT') {
            $params = ['json' => $params];
        }
        try {
            $response = $this->client->request($method, $endpoint, $params);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $msg = $response->getBody();
            $code = $response->getStatusCode();
            $previous = $e;
            throw new StreamFeedException($msg, $code, $previous);
        }
        return $response;
    }

    /**
     * @param string $userId
     * @param array $data
     * @param bool $getOrCreate
     *
     * @return array
     */
    public function add($userId, array $data=null, $getOrCreate=null)
    {
        $endpoint = 'user/';
        $payload = [
            'id' => $userId,
        ];
        if ($data !== null) {
            $payload['data'] = $data;
        }
        if ($getOrCreate) {
            $endpoint .= '?get_or_create=true';
        }
        $response = $this->doRequest('POST', $endpoint, $payload);
        $body = $response->getBody()->getContents();
        return json_decode($body, true);
    }

    /**
     * @param string $userId
     *
     * @return string
     */
    public function createReference($userId)
    {
        $myUserId = $userId;
        if (is_array($userId) && array_key_exists('id', $userId)) {
            $myUserId = $userId['id'];
        }
        return 'SU:' . $myUserId;
    }

    /**
     * @param string $userId
     *
     * @return array
     */
    public function delete($userId)
    {
        $response = $this->doRequest('DELETE', 'user/' . $userId . '/');
        $body = $response->getBody()->getContents();
        return json_decode($body, true);
    }

    /**
     * @param string $userId
     *
     * @return array
     */
    public function get($userId)
    {
        $response = $this->doRequest('GET', 'user/' . $userId . '/');
        $body = $response->getBody()->getContents();
        return json_decode($body, true);
    }

    /**
     * @param string $userId
     * @param array $data

     * @return array
     */
    public function update($userId, array $data=null)
    {
        $payload = [];
        if ($data !== null) {
            $payload['data'] = $data;
        }
        $response = $this->doRequest('PUT', 'user/' . $userId . '/', $payload);
        $body = $response->getBody()->getContents();
        return json_decode($body, true);
    }
}
