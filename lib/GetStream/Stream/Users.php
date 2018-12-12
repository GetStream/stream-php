<?php

namespace GetStream\Stream;

use Firebase\JWT\JWT;
use GetStream\Stream\Client as StreamClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;

class Users
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
        $this->client = new GuzzleClient([
            'base_uri' => $streamClient->getBaseUrl().'/'.$streamClient->api_version.'/',
            'timeout' => $streamClient->timeout,
            'handler' => $this->handlerStack(),
        ]);
    }

    private function doRequest($method, $endpoint, $params=null)
    {
        if($params === null){
            $params = array();
        }
        if( $method === 'POST' || $method === 'PUT' ){
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
    public function add(string $userId, array $data=null, bool $getOrCreate=false)
    {
        $endpoint = 'user/';
        $payload = [
            'id' => $userId,
        ];
        if( $data !== null ){
            $payload['data'] = $data;
        }
        if($getOrCreate){
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
    public function createReference(string $userId)
    {
        $myUserId = $userId;
        if(is_array($userId) && in_array('id', $userId)){
            $myUserId = $userId['id'];
        }
        return 'SU:' . $myUserId;
    }

    /**
     * @param string $userId
     *
     * @return array
     */
    public function delete(string $userId)
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
    public function get(string $userId)
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
    public function update(string $userId, array $data=null)
    {
        $payload = [];
        if( $data !== null ){
            $payload['data'] = $data;
        }
        $response = $this->doRequest('PUT', 'user/' . $userId . '/', $payload);
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
            'resource' => 'users',
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
