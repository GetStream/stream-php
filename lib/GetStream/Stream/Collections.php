<?php

namespace GetStream\Stream;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;

class Collections
{
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
            'base_uri' => $streamClient->getBaseUrl().'/'.$streamClient->api_version.'/',
            'timeout' => $streamClient->timeout,
            'handler' => Util::handlerStack($apiKey, $apiSecret, 'collections'),
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
        if ($method === 'GET' && $params !== null) {
            $endpoint .= '?' . http_build_query($params);
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
     * @param string $collectionName
     * @param array $data
     * @param string $id (optional)
     * @param string $user_id (optional)
     *
     * @return array
     */
    public function add($collectionName, array $data, $id=null, $user_id=null)
    {
        $payload = ["id" => $id, "data" => $data, "user_id" => $user_id];
        $response = $this->doRequest('POST', 'collections/' . $collectionName . '/', $payload);
        $body = $response->getBody()->getContents();
        return json_decode($body, true);
    }

    /**
     * @param string $collectionName
     * @param string $id
     *
     * @return string
     */
    public function createReference($collectionName, $id)
    {
        return "SO:".$collectionName.":".$id;
    }

    /**
     * @param string $collectionName
     * @param string $id
     *
     * @return array
     */
    public function delete($collectionName, $id)
    {
        $response = $this->doRequest('DELETE', 'collections/' . $collectionName . '/' . $id . '/');
        $body = $response->getBody()->getContents();
        return json_decode($body, true);
    }

    /**
     * @param string $collectionName
     * @param array $ids
     *
     * @return array
     */
    public function deleteMany($collectionName, array $ids)
    {
        $ids = join(',', $ids);
        $queryParams = ['collection_name' => $collectionName, 'ids' => $ids];
        $response = $this->client->request('DELETE', 'collections/?'.http_build_query($queryParams));
        $body = $response->getBody()->getContents();
        return json_decode($body, true);
    }

    /**
     * @param string $collectionName
     * @param string $id
     *
     * @return array
     */
    public function get($collectionName, $id)
    {
        $response = $this->doRequest('GET', 'collections/' . $collectionName . '/' . $id . '/');
        $body = $response->getBody()->getContents();
        return json_decode($body, true);
    }

    /**
     * @param string $collectionName
     * @param array $ids
     *
     * @return array
     */
    public function select($collectionName, array $ids)
    {
        $mappedIds = array_map(function ($id) use ($collectionName) {
            return sprintf('%s:%s', $collectionName, $id);
        }, $ids);
        $params = ['foreign_ids' => join(',', $mappedIds)];
        $response = $this->doRequest('GET', 'meta/', $params);
        $body = $response->getBody()->getContents();
        return json_decode($body, true);
    }

    /**
     * @param string $collectionName
     * @param string $id
     * @param array $data
     *
     * @return array
     */
    public function update($collectionName, $id, array $data)
    {
        $payload = ["data" => $data];
        $response = $this->doRequest('PUT', 'collections/' . $collectionName . '/' . $id . '/', $payload);
        $body = $response->getBody()->getContents();
        return json_decode($body, true);
    }

    /**
     * @param string $collectionName
     * @param array $data
     *
     * @return array
     */
    public function upsert($collectionName, array $data)
    {
        if (!is_array($data)) {
            $data = [$data];
        }
        $response = $this->doRequest('POST', 'meta/', ['data' => [$collectionName => $data]]);
        $body = $response->getBody()->getContents();
        return json_decode($body, true);
    }
}
