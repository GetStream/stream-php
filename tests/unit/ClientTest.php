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
        $this->assertSame($feed1->getId(), 'flat:1');
    }

    public function testCreateReference()
    {
        $client = new Client('key', 'secret', $location='qa');
        $ref = $client->collections()->createReference("item", "42");
        $this->assertEquals($ref, "SO:item:42");
    }

    public function testCreateUserReference()
    {
        $client = new Client('key', 'secret', $location='qa');
        $ref = $client->users()->createReference("42");
        $this->assertEquals($ref, "SU:42");
    }

    public function testGetActivitiesByForeignIdExceptionNoArray()
    {
        $this->expectException(\GetStream\Stream\StreamFeedException::class);
        $client = new Client('key', 'secret');
        $client->getActivitiesByForeignId([1, 2]);
    }

    public function testGetActivitiesByForeignIdExceptionMalformedArray()
    {
        $this->expectException(\GetStream\Stream\StreamFeedException::class);
        $client = new Client('key', 'secret');
        $client->getActivitiesByForeignId([[1, 2], [2, 3, 4]]);
    }

    public function testEnvironmentVariable()
    {
        // Arrange
        $previous = getenv('STREAM_BASE_URL');
        putenv('STREAM_BASE_URL=test.stream-api.com/api');
        $client = new Client('key', 'secret');

        // Act
        $baseUrl = $client->getBaseUrl();

        // Assert
        $this->assertSame('test.stream-api.com/api', $baseUrl);

        // Teardown
        if ($previous === false) {
            // Remove the environment variable.
            putenv('STREAM_BASE_URL');
        } else {
            putenv('STREAM_BASE_URL='.$previous);
        }
    }
}
