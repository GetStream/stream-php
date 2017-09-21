<?php

namespace GetStream\Unit;

use GetStream\Stubs\BaseFeedStub;
use PHPUnit\Framework\TestCase;

class BaseFeedTest extends TestCase
{
    public function testClientFeedAddActivity()
    {
        $feed = new BaseFeedStub(null, 'feed', '1', 'api', 'token');
        $data = ['name' => 'php client'];
        extract($feed->addActivity($data));
        $this->assertSame($uri, 'feed/feed/1/');
    }

    /**
     * @expectedException GetStream\Stream\StreamFeedException
     */
    public function testValidateSlug()
    {
        new BaseFeedStub(null, 'feed-ko', '1', 'api', 'token');
    }

    /**
     * @expectedException GetStream\Stream\StreamFeedException
     */
    public function testValidateUserId()
    {
        new BaseFeedStub(null, 'feed_ko', 'ko:1', 'api', 'token');
    }

    public function testDashIsOkUserId()
    {
        new BaseFeedStub(null, 'feed_ko', 'ko-1', 'api', 'token');
    }

    public function testGetToken()
    {
        $feed = new BaseFeedStub(null, 'feed', '1', 'api', 'token');
        $this->assertSame($feed->getToken(), 'token');
    }

    public function testClientFeedGetActivities()
    {
        $feed = new BaseFeedStub(null, 'feed', '1', 'api', 'token');

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
        $feed = new BaseFeedStub(null, 'feed', '1', 'api', 'token');
        $aid = '123';
        extract($feed->removeActivity($aid));
        $this->assertSame($uri, 'feed/feed/1/123/');
    }
}
