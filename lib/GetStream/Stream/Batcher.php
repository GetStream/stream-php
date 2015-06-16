<?php
namespace GetStream\Stream;

use Exception;
use GuzzleHttp;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Stream\Stream;

class Batcher extends Feed
{

    /**
     * @var HttpSignatures\Context
     */
    private $ctx;

    /**
     * @return \GuzzleHttp\Client
     */
    public function getHttpClient()
    {
        $client = new GuzzleHttp\Client();
        $client->getEmitter()->attach(new SignRequestSubscriber($this->ctx));
        return $client;
    }

    /**
     * @param  string $resource
     * @param  string $action
     * @return array
     */
    protected function getHttpRequestHeaders($resource, $action)
    {
        return [
            'Content-Type'      => 'application/json',
            'Date'              =>  gmdate('D, d M Y H:i:s T'),
            'X-Api-Key'         =>  $this->api_key
        ];
    }

    /**
     * @var HttpSignatures\Context
     */
    public function __construct($client, $context, $api_key)
    {
        $this->client = $client;
        $this->ctx = $context;
        $this->api_key = $api_key;
    }

    /**
     * @param  array $activityData
     * @param  array $feeds
     * @return array
     */
    public function addToMany($activityData, $feeds)
    {
        $data = [
            'feeds' => $feeds,
            'activity' => $activityData
        ];
        return $this->makeHttpRequest('feed/add_to_many/', 'POST', $data);
    }

    /**
     * @param  array $follows
     * @return array
     *
     * $follows = [
     *   ['source' => 'flat:1', 'target' => 'user:1'],
     *   ['source' => 'flat:1', 'target' => 'user:3']
     * ]
     */
    public function followMany($follows)
    {
        return $this->makeHttpRequest('follow_many/', 'POST', $follows);
    }

    /**
     * @param  string $method
     */
    public function test($method)
    {
        return $this->makeHttpRequest('test/auth/digest/', $method);
    }
}
