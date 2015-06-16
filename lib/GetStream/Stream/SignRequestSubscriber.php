<?php

namespace GetStream\Stream;

use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\RequestEvents;
use GuzzleHttp\Event\SubscriberInterface;
use HttpSignatures\GuzzleHttp\Message;
use HttpSignatures\Context;
use HttpSignatures\GuzzleHttp\MessageHeaders;

class SignRequestSubscriber implements SubscriberInterface
{
    /**
     * @var \HttpSignatures\Context
     */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function getEvents()
    {
        return ['before' => ['onBefore', RequestEvents::SIGN_REQUEST]];
    }

    public function onBefore(BeforeEvent $event)
    {
        $request = $event->getRequest();
        $this->context->signer()->sign(new Message($request));
        $headers = new MessageHeaders($request);
        $headers->set("Authorization", $headers->get("Signature"));
        $request->removeHeader("Signature");

    }
}
