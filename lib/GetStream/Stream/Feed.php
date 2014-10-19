<?php
namespace GetStream\Stream;

use Exception;
use GuzzleHttp;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Stream\Stream;

class Feed extends BaseFeed
{
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var array
     */
    protected $httpRequestHeaders = [];

    /**
     * @return \GuzzleHttp\Client
     */
    public static function getHttpClient()
    {
        return new GuzzleHttp\Client();
    }

    /**
     * @param  string $uri
     * @return string
     */
    protected function buildRequestUrl($uri)
    {
        if (null === $this->baseUrl) {
            if (getenv('LOCAL')) {
                $this->baseUrl = 'http://localhost:8000/api';
            } else {
                $this->baseUrl = static::API_ENDPOINT;
            }
        }

        return $this->baseUrl . '/' . $uri;
    }

    /**
     * @return array
     */
    protected function getHttpRequestHeaders()
    {
        if (empty($this->httpRequestHeaders)) {
            $feed_name = Client::validateFeed($this->feed);

            $this->httpRequestHeaders = [
                'Authorization' => "{$feed_name} {$this->token}",
                'Content-Type'  => 'application/json',
            ];
        }

        return $this->httpRequestHeaders;
    }

    /**
     * @param  string $uri
     * @param  string $method
     * @param  array $data
     * @param  array $query_params
     * @return mixed
     * @throws StreamFeedException
     */
    public function makeHttpRequest($uri, $method, $data = [], $query_params = [])
    {
        $query_params['api_key'] = $this->api_key;

        $client = static::getHttpClient();
        $request = $client->createRequest($method, $this->buildRequestUrl($uri));
        $request->setHeaders($this->getHttpRequestHeaders());

        $query = $request->getQuery();
        foreach ($query_params as $key => $value) {
            $query[$key] = $value;
        }

        $json_data = json_encode($data);
        $request->setBody(Stream::factory($json_data));

        try {
            $response = $client->send($request);
        } catch (Exception $e) {
            if ($e instanceof ClientException) {
                throw new StreamFeedException($e->getResponse()->getBody());
            } else {
                throw $e;
            }
        }

        return $response->json();
    }
}
