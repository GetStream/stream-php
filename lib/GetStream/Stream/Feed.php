<?php
namespace GetStream\Stream;

use GuzzleHttp;
use GuzzleHttp\Exception\ClientErrorResponseException;
use GuzzleHttp\Exception\ServerErrorResponse;

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
        $request->setQuery($query_params);

        $postBody = $request->getBody();

        $data = is_null($data) ? array() : $data;
        foreach ($data as $key => $value) {
            $postBody->setField($key, $value);
        }

        try {
            $response = $client->send($request);
        } catch (Exception $e) {
            if ($e instanceof ClientErrorResponseException || $e instanceof ServerErrorResponse) {
                throw new StreamFeedException($e->getResponse());
            } else {
                throw $e;
            }
        }
        return $response->json();
    }
}
