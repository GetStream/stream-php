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
        $this->assertSame($url, 'asdfg://api.getstream.io/api/v1.0/x');
    }

    public function testClientHostnames()
    {
        $client = new Client('key', 'secret');
        $client->setLocation('qa');
        $url = $client->buildRequestUrl('x');
        $this->assertSame($url, 'http://qa-api.getstream.io/api/v1.0/x');

        $client = new Client('key', 'secret', $api_version='1234', $location='asdfg');
        $url = $client->buildRequestUrl('y');
        $this->assertSame($url, 'https://asdfg-api.getstream.io/api/1234/y');

        $client = new Client('key', 'secret');
        $client->setLocation('us-east');
        $url = $client->buildRequestUrl('z');
        $this->assertSame($url, 'https://us-east-api.getstream.io/api/v1.0/z');
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
