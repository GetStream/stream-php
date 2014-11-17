<?php

namespace GetStream\Stream;

class _BaseFeed extends BaseFeed
{
    public function makeHttpRequest($uri, $method, $data = null, $query_params = null)
    {
        return [
            'uri'          => $uri,
            'method'       => $method,
            'data'         => $data,
            'query_params' => $query_params,
        ];
    }
}

class BaseFeedTest extends \PHPUnit_Framework_TestCase
{
    public function testClientFeedAddActivity()
    {
        $feed = new _BaseFeed(null, 'feed', '1', 'api', 'token');
        $data = ['name' => 'php client'];
        extract($feed->addActivity($data));
        $this->assertSame($uri, 'feed/feed/1/');
    }

    /**
     * @expectedException GetStream\Stream\StreamFeedException
     */
    public function testValidateSlug()
    {
        new _BaseFeed(null, 'feed-ko', '1', 'api', 'token');
    }

    /**
     * @expectedException GetStream\Stream\StreamFeedException
     */
    public function testValidateUserId()
    {
        new _BaseFeed(null, 'feed_ko', 'ko-1', 'api', 'token');
    }

    public function testGetToken()
    {
        $feed = new _BaseFeed(null, 'feed', '1', 'api', 'token');
        $this->assertSame($feed->getToken(), 'token');
    }

    public function testClientFeedGetActivities()
    {
        $feed = new _BaseFeed(null, 'feed', '1', 'api', 'token');

        $limit = 1;
        $offset = 3;

        extract($feed->getActivities($offset, $limit));
        $this->assertSame($uri, 'feed/feed/1/');

        extract($feed->getActivities($offset));
        $this->assertSame($uri, 'feed/feed/1/');

        extract($feed->getActivities());
        $this->assertSame($uri, 'feed/feed/1/');
    }

    public function testClientRemoveActivity()
    {
        $feed = new _BaseFeed(null, 'feed', '1', 'api', 'token');
        $aid = '123';
        extract($feed->removeActivity($aid));
        $this->assertSame($uri, 'feed/feed/1/123/');
    }
}
