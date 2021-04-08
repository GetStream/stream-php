<?php

namespace GetStream\Stream;

use Firebase\JWT\JWT;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;

class Util
{
    /**
     * @return HandlerStack
     */
    public static function handlerStack($apiKey, $apiSecret, $resource)
    {
        $token = JWT::encode([
            'action' => '*',
            'user_id' => '*',
            'feed_id' => '*',
            'resource' => $resource,
        ], $apiSecret, 'HS256');
        $stack = HandlerStack::create();
        $stack->push(function (callable $handler) use ($token, $apiKey) {
            return function (RequestInterface $request, array $options) use ($handler, $token, $apiKey) {
                // Add authentication headers.
                $request = $request
                    ->withAddedHeader('Authorization', $token)
                    ->withAddedHeader('Stream-Auth-Type', 'jwt')
                    ->withAddedHeader('Content-Type', 'application/json')
                    ->withAddedHeader('X-Stream-Client', 'stream-php-client-' . Constant::VERSION);
                // Add a api_key query param.
                $queryParams = \GuzzleHttp\Psr7\parse_query($request->getUri()->getQuery());
                $query = http_build_query($queryParams + ['api_key' => $apiKey]);
                $request = $request->withUri($request->getUri()->withQuery($query));
                return $handler($request, $options);
            };
        });
        return $stack;
    }
}
