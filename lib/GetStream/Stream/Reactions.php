<?php

namespace GetStream\Stream;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;

class Reactions
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
     * @param ClientInterface $streamClient
     * @param string $apiKey
     * @param string $apiSecret
     */
    public function __construct(ClientInterface  $streamClient, $apiKey, $apiSecret)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->client = new GuzzleClient([
            'base_uri' => $streamClient->getBaseUrl().'/'.$streamClient->api_version.'/',
            'timeout' => $streamClient->timeout,
            'handler' => Util::handlerStack($apiKey, $apiSecret, 'reactions'),
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
     * @param string $kind
     * @param string $activityIid
     * @param string $userId
     * @param array $data
     * @param array $targetFeeds
     *
     * @return array
     */
    public function add($kind, $activityId, $userId, array $data=null, array $targetFeeds=null)
    {
        $payload = [
            'kind' => $kind,
            'activity_id' => $activityId,
            'user_id' => $userId,
        ];
        if ($data !== null) {
            $payload['data'] = $data;
        }
        if ($targetFeeds !== null) {
            $payload['target_feeds'] = $targetFeeds;
        }
        $response = $this->doRequest('POST', 'reaction/', $payload);
        $body = $response->getBody()->getContents();
        return json_decode($body, true);
    }

    /**
     * @param string $kind
     * @param string $parentId
     * @param string $userId
     * @param array $data
     * @param array $targetFeeds
     *
     * @return array
     */
    public function addChild($kind, $parentId, $userId, array $data=null, array $targetFeeds=null)
    {
        $payload = [
            'kind' => $kind,
            'parent' => $parentId,
            'user_id' => $userId,
        ];
        if ($data !== null) {
            $payload['data'] = $data;
        }
        if ($targetFeeds !== null) {
            $payload['target_feeds'] = $targetFeeds;
        }
        $response = $this->doRequest('POST', 'reaction/', $payload);
        $body = $response->getBody()->getContents();
        return json_decode($body, true);
    }

    /**
     * @param string $reactionId
     *
     * @return string
     */
    public function createReference($reactionId)
    {
        $myReactionId = $reactionId;
        if (is_array($reactionId) && array_key_exists('id', $reactionId)) {
            $myReactionId = $reactionId['id'];
        }
        return 'SR:' . $myReactionId;
    }

    /**
     * @param string $reactionId
     *
     * @return array
     */
    public function delete($reactionId)
    {
        $response = $this->doRequest('DELETE', 'reaction/' . $reactionId . '/');
        $body = $response->getBody()->getContents();
        return json_decode($body, true);
    }

    /**
     * @param string $lookupField
     * @param string $lookupValue
     * @param string $kind
     * @param array $params // for pagination parameters e.g. ["limit" => "10"]
     *
     * @return array
     */
    public function filter($lookupField, $lookupValue, $kind=null, array $params=null)
    {
        if (!in_array($lookupField, ["reaction_id", "activity_id", "user_id"])) {
            throw StreamFeedException("Invalid request parameters");
        }
        $endpoint = "reaction/" . $lookupField . "/" . $lookupValue . "/";
        if ($kind !== null) {
            $endpoint .= $kind . "/";
        }
        $response = $this->doRequest('GET', $endpoint, $params);
        $body = $response->getBody()->getContents();
        return json_decode($body, true);
    }


    /**
     * @param string $reactionId
     *
     * @return array
     */
    public function get($reactionId)
    {
        $response = $this->doRequest('GET', 'reaction/' . $reactionId . '/');
        $body = $response->getBody()->getContents();
        return json_decode($body, true);
    }

    /**
     * @param string $reactionId
     * @param array $data
     * @param array $targetFeeds
     *
     * @return array
     */
    public function update($reactionId, array $data=null, array $targetFeeds=null)
    {
        $payload = [];
        if ($data !== null) {
            $payload['data'] = $data;
        }
        if ($targetFeeds !== null) {
            $payload['target_feeds'] = $targetFeeds;
        }
        $response = $this->doRequest('PUT', 'reaction/' . $reactionId . '/', $payload);
        $body = $response->getBody()->getContents();
        return json_decode($body, true);
    }
}
