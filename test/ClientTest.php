<?php

namespace GetStream\Stream;

class ClientTest extends \PHPUnit_Framework_TestCase
{
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
        $client = new Client('key', 'secret');
        $feed1 = $client->feed('flat', '1');
    }
}
