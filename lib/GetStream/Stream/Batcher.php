<?php
namespace GetStream\Stream;

use GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;

class Batcher extends Feed
{
    /**
     * @var Signer
     */
    private $signer;

    /**
     * @param Client $client
     * @param Signer $signer
     * @param string $api_key
     */
    public function __construct($client, Signer $signer, $api_key)
    {
        $this->client = $client;
        $this->signer = $signer;
        $this->api_key = $api_key;
    }

    /**
     * @return \GuzzleHttp\HandlerStack
     */
    public function getHandlerStack()
    {
        $stack = HandlerStack::create();
        $stack->push(function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                return $handler($this->signer->signRequest($request), $options);
            };
        });

        return $stack;
    }

    /**
     * @param array $activityData
     * @param array $feeds
     *
     * @throws StreamFeedException
     *
     * @return array
     */
    public function addToMany($activityData, $feeds)
    {
        $data = [
            'feeds' => $feeds,
            'activity' => $activityData,
        ];

        return $this->makeHttpRequest('feed/add_to_many/', 'POST', $data);
    }

    /**
     * @param string $resource
     * @param string $action
     *
     * @return array
     */
    protected function getHttpRequestHeaders($resource, $action)
    {
        return [
            'Content-Type' => 'application/json',
            'Date' =>  gmdate('D, d M Y H:i:s T'),
            'X-Api-Key' => $this->api_key,
        ];
    }

    /**
     * @param array $follows
     * @param int $activity_copy_limit
     *
     * @throws StreamFeedException
     *
     * @return array
     * $follows = [
     *   ['source' => 'flat:1', 'target' => 'user:1'],
     *   ['source' => 'flat:1', 'target' => 'user:3']
     * ]
     */
    public function followMany($follows, $activity_copy_limit = null)
    {
        $query_params = [];
        if ($activity_copy_limit !== null) {
          $query_params["activity_copy_limit"] = $activity_copy_limit;
        }
        return $this->makeHttpRequest('follow_many/', 'POST', $follows, $query_params);
    }

    /**
     * @param string $method
     *
     * @throws StreamFeedException
     *
     * @return mixed
     */
    public function test($method)
    {
        return $this->makeHttpRequest('test/auth/digest/', $method);
    }
}
