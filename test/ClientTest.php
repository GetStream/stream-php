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
        $feed1 = $client->feed('flat:1');
    }

    public function testClientFeedAddActivity()
    {
        $feed = new HttpBinFeed('feed:1', 'api', 'token');
        $data = ["name" => "php client"];
        $response = $feed->addActivity($data);
        $this->assertSame($response["args"], ["api_key" => "api"]);
        $this->assertSame($response["form"], $data);
    }

    public function testClientFeedGetActivities()
    {
        $api = 'api';
        $feed = new HttpBinFeed('feed:1', $api, 'token');

        $limit = 1;
        $offset = 3;

        $response = $feed->getActivities($offset, $limit);
        $this->assertSame($response["args"]["limit"], "$limit");
        $this->assertSame($response["args"]["offset"], "$offset");

        $response = $feed->getActivities($offset);
        $this->assertSame($response["args"]["limit"], "20");
        $this->assertSame($response["args"]["offset"], "$offset");

        $response = $feed->getActivities();
        $this->assertSame($response["args"]["limit"], "20");
        $this->assertSame($response["args"]["offset"], "0");
    }

    public function testClientremoveActivity()
    {
        $feed = new HttpBinFeed('feed:1', 'api', 'token');
        $aid = '123';
        $response = $feed->removeActivity($aid);
    }
}
