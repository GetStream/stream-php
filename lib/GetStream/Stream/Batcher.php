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
        $client = new GuzzleHttp\Client([
            'defaults' => [
            	'auth' => 'http-signatures'
            ]
        ]);
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

	public function test($method)
	{
		return $this->makeHttpRequest('test/auth/digest/', $method);
	}
}
