<?php

namespace GetStream\Stream;

use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Subscriber\History;

class TestFeed extends Feed
{
    public static $history = null;

    public static function getHistory()
    {
        if (is_null(TestFeed::$history)) {
            TestFeed::$history = new History();
        }
        return TestFeed::$history;
    }

    public static function getHttpClient()
    {
        $client = parent::getHttpClient();
        $mock = new Mock([
            new Response(200, ['X-Foo' => 'Bar']),
            "HTTP/1.1 202 OK\r\nContent-Length: 0\r\n\r\n"
        ]);
        $client->getEmitter()->attach(TestFeed::getHistory());
        $client->getEmitter()->attach($mock);
        return $client;
    }
}


class FeedTest extends \PHPUnit_Framework_TestCase
{

    public function testClientFeedAddActivity()
    {
        $feed = new TestFeed('feed:1', 'api', 'token');
        $data = ["name" => "php client"];
        $feed->addActivity($data);
        $lastReq = TestFeed::getHistory()->getLastRequest();
        $this->assertSame($lastReq->getUrl(), "https://getstream.io/api/feed/feed/1/?api_key=api");
        $this->assertSame($lastReq->getMethod(), "POST");
    }

    public function testClientFeedGetActivities()
    {
        $api = 'api';
        $feed = new TestFeed('feed:1', $api, 'token');

        $limit = 1;
        $offset = 3;

        $response = $feed->getActivities($offset, $limit);
        $lastReq = TestFeed::getHistory()->getLastRequest();
        $this->assertSame($lastReq->getUrl(), "https://getstream.io/api/feed/feed/1/?offset=3&limit=1&api_key=api");

        $response = $feed->getActivities($offset);
        $lastReq = TestFeed::getHistory()->getLastRequest();
        $this->assertSame($lastReq->getUrl(), "https://getstream.io/api/feed/feed/1/?offset=3&limit=20&api_key=api");

        $response = $feed->getActivities();
        $lastReq = TestFeed::getHistory()->getLastRequest();
        $this->assertSame($lastReq->getUrl(), "https://getstream.io/api/feed/feed/1/?offset=0&limit=20&api_key=api");
    }

    public function testClientremoveActivity()
    {
        $feed = new TestFeed('feed:1', 'api', 'token');
        $aid = '123';
        $response = $feed->removeActivity($aid);
        $lastReq = TestFeed::getHistory()->getLastRequest();
        $this->assertSame($lastReq->getUrl(), "https://getstream.io/api/feed/feed/1/123/?api_key=api");
        $this->assertSame($lastReq->getMethod(), "DELETE");
    }

    public function testClientFollow()
    {
        $feed = new TestFeed('feed:1', 'api', 'token');
        $target = 'feed:123';
        $response = $feed->followFeed($target);
        $lastReq = TestFeed::getHistory()->getLastRequest();
        $this->assertSame($lastReq->getUrl(), "https://getstream.io/api/feed/feed/1/follows/?api_key=api");
        $this->assertSame($lastReq->getMethod(), "POST");
    }

    public function testClientUnfollow()
    {
        $feed = new TestFeed('feed:1', 'api', 'token');
        $target = 'feed:123';
        $response = $feed->unfollowFeed($target);
        $lastReq = TestFeed::getHistory()->getLastRequest();
        $this->assertSame($lastReq->getUrl(), "https://getstream.io/api/feed/feed/1/follows/feed:123/?api_key=api");
        $this->assertSame($lastReq->getMethod(), "DELETE");
    }
}
