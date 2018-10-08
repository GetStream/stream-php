<?php

namespace GetStream\Stream;

use Firebase\JWT\JWT;
use Psr\Http\Message\RequestInterface;

class Signer
{
    /**
     * @var string
     */
    private $api_key;

    /**
     * @var string
     */
    private $api_secret;

    /**
     * @param string $api_key
     * @param string $api_secret
     */
    public function __construct($api_key, $api_secret)
    {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
    }

    /**
     * @param  string $value
     * @return string
     */
    public function signature($value)
    {
        $digest = hash_hmac('sha1', $value, sha1($this->api_secret, true), true);

        return trim(strtr(base64_encode($digest), '+/', '-_'), '=');
    }

    /**
     * @param RequestInterface $request
     *
     * @return RequestInterface
     */
    public function signRequest(RequestInterface $request)
    {
        $signatureString = sprintf(
            "(request-target): %s %s\ndate: %s",
            mb_strtolower($request->getMethod()),
            $request->getRequestTarget(),
            $request->getHeaderLine('date')
        );

        $signature = base64_encode(hash_hmac('sha256', $signatureString, $this->api_secret, true));

        $header = sprintf(
            'Signature keyId="%s",algorithm="hmac-sha256",headers="(request-target) date",signature="%s"',
            $this->api_key,
            $signature
        );

        return $request->withHeader('Authorization', $header);
    }

    /**
     * @param  string $feedId
     * @param  string $resource
     * @param  string $action
     * @return string
     */
    public function jwtScopeToken($feedId, $resource, $action)
    {
        $payload = [
            'action'   => $action,
            'feed_id'  => $feedId,
            'resource' => $resource,
        ];

        return JWT::encode($payload, $this->api_secret, 'HS256');
    }

    /**
     * @param  string $user_id
     * @param  string $extra_data
     * @return string
     */
    public function jwtUserSessionToken(string $user_id, array $extra_data)
    {
        $payload = [
            'user_id'   => $user_id,
        ];
        foreach($extra_data as $name => $value){
            $payload[$name] = $value;
        }
        return JWT::encode($payload, $this->api_secret, 'HS256');
    }

}
