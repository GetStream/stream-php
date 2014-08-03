<?php
namespace GetStream\Stream;

use GuzzleHttp;
use GuzzleHttp\Exception\ClientErrorResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerErrorResponse;
use \Exception;


class Feed extends BaseFeed
{
    public static function getHttpClient()
    {
        return new GuzzleHttp\Client();
    }

    public function makeHttpRequest($uri, $method, $data = null, $query_params = null)
    {
        $client = static::getHttpClient();
        $url = static::API_ENDPOINT . "/{$uri}";
        $feed_name = Client::validateFeed($this->feed);
        $query_params = is_null($query_params) ? array() : $query_params;
        $query_params['api_key'] = $this->api_key;

        $request = $client->createRequest($method, $url);
        $request->setHeader('Authorization', "{$feed_name} {$this->token}");
        $request->setHeader('Content-Type', "application/json");
        $request->setQuery($query_params);

        $data = is_null($data) ? array() : $data;
        $json_data = json_encode($data);
        $request->setBody(GuzzleHttp\Stream\Stream::factory($json_data));

        try {
            $response = $client->send($request);
        } catch (Exception $e) {
            if ($e instanceof ClientErrorResponseException || $e instanceof ServerErrorResponse || $e instanceof ClientException) {
                throw new StreamFeedException($e->getResponse()->getBody());
            } else {
                throw $e;
            }
        }
        return $response->json();
    }
}
