<?php

namespace GetStream\Unit;

use GetStream\Stream\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testClientSetProtocol()
    {
        $client = new Client('key', 'secret');
        $client->setProtocol('asdfg');
        $url = $client->buildRequestUrl('x');
        $this->assertSame('asdfg://api.stream-io-api.com/api/v1.0/x', $url);
    }

    public function testClientHostnames()
    {
        $client = new Client('key', 'secret');
        $client->setLocation('qa');
        $url = $client->buildRequestUrl('x');
        $this->assertSame('https://qa-api.stream-io-api.com/api/v1.0/x', $url);

        $client = new Client('key', 'secret', $api_version = '1234', $location = 'asdfg');
        $url = $client->buildRequestUrl('y');
        $this->assertSame('https://asdfg-api.stream-io-api.com/api/1234/y', $url);

        $client = new Client('key', 'secret');
        $client->setLocation('us-east');
        $url = $client->buildRequestUrl('z');
        $this->assertSame('https://us-east-api.stream-io-api.com/api/v1.0/z', $url);
    }

    public function testClientSigning()
    {
        $client = new Client('key', 'secret');
        $digested = $client->signer->signature('feed:1');
        $this->assertEquals('_uLo-YmjaGyY3u6NJTXw_fHdFBM', $digested);
        $digested2 = $client->signer->signature('feed:2');
        $this->assertNotEquals($digested2, $digested);
    }

    public function testClientFeed()
    {
        $client = new Client('key', 'secret', $location='qa');
        $feed1 = $client->feed('flat', '1');
    }
}
