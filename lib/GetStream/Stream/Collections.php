<?php

namespace GetStream\Stream;

use Firebase\JWT\JWT;
use GetStream\Stream\Client as StreamClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;

class Collections
{
    /**
     * @var Client
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
     * @param \GetStream\Stream\Client $streamClient
     * @param string $apiKey
     * @param string $apiSecret
     */
    public function __construct(StreamClient $streamClient, $apiKey, $apiSecret)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->client = new Client([
            'base_uri' => $streamClient->getBaseUrl().'/'.$streamClient->api_version.'/',
            'timeout' => $streamClient->timeout,
            'handler' => $this->handlerStack(),
        ]);
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

        $queryParams = ['foreign_ids' => join(',', $mappedIds)];

        try {
            $response = $this->client->request('GET', 'meta/?'.http_build_query($queryParams));
        } catch (ClientException $e) {
            throw new StreamFeedException($e->getResponse()->getBody());
        }

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
        $options = ['json' => ['data' => [$collectionName => $data]]];

        try {
            $response = $this->client->request('POST', 'meta/', $options);
        } catch (ClientException $e) {
            throw new StreamFeedException($e->getResponse()->getBody());
        }

        $body = $response->getBody()->getContents();

        return json_decode($body, true);
    }

    /**
     * @param string $collectionName
     * @param array $ids
     *
     * @return array
     */
    public function delete($collectionName, array $ids)
    {
        $options = ['json' => ['collection_name' => $collectionName, 'ids' => $ids]];

        try {
            $response = $this->client->request('DELETE', 'meta/', $options);
        } catch (ClientException $e) {
            throw new StreamFeedException($e->getResponse()->getBody());
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
            'resource' => 'collections',
        ], $this->apiSecret, 'HS256');

        $stack = HandlerStack::create();
        $stack->push(function (callable $handler) use ($token) {
            return function (RequestInterface $request, array $options) use ($handler, $token) {
                // Add authentication headers.
                $request = $request
                    ->withAddedHeader('Authorization', $token)
                    ->withAddedHeader('Stream-Auth-Type', 'jwt')
                    ->withAddedHeader('Content-Type', 'application/json')
                    ->withAddedHeader('X-Stream-Client', 'stream-php-client-' . VERSION);

                // Add a api_key query param.
                $queryParams = \GuzzleHttp\Psr7\parse_query($request->getUri()->getQuery());
                $query = http_build_query($queryParams + ['api_key' => $this->apiKey]);
                $request = $request->withUri($request->getUri()->withQuery($query));

                return $handler($request, $options);
            };
        });

        return $stack;
    }
}
