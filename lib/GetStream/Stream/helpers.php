<?php
namespace GetStream\Stream;

use HttpSignatures\Context;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\MessageInterface;

class Message
{
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var MessageHeaders
     */
    public $headers;
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
        $this->headers = new MessageHeaders($request);
    }
    public function getQueryString()
    {
        return $this->request->getUri()->getQuery();
    }
    public function getMethod()
    {
        return $this->request->getMethod();
    }
    public function getPathInfo()
    {
        return $this->request->getUri()->getPath();
    }
}


class MessageHeaders
{
    /**
     * @var MessageInterface
     */
    private $request;
    public function __construct(MessageInterface $request)
    {
        $this->request = $request;
    }
    public function has($header)
    {
        return $this->request->hasHeader($header);
    }
    public function get($header)
    {
        return $this->request->getHeader($header)[0];
    }
    public function set($header, $value)
    {
        $this->request = $this->request->withHeader($header, $value);
    }
}


function signature_middleware_factory(Context $context)
{
    return function (callable $handler) use ($header, $context) {
        return function (RequestInterface $request, array $options) use ($handler, $context) {
            $message = new Message($request);
            $context->signer()->sign($message);
            $request = $request->withHeader("Authorization", $message->headers->get("Signature"));
            return $handler($request, $options);
        };
    };
}
