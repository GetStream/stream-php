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
        $response = $feed->addActivity($data);
        $this->assertSame('feed/feed/1/', $response['uri']);
    }

    /**
     * @expectedException \GetStream\Stream\StreamFeedException
     */
    public function testValidateSlug()
    {
        new BaseFeedStub(null, 'feed-ko', '1', 'api', 'token');
    }

    /**
     * @expectedException \GetStream\Stream\StreamFeedException
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
        $this->assertSame('token', $feed->getToken());
    }

    public function testClientFeedGetActivities()
    {
        $feed = new BaseFeedStub(null, 'feed', '1', 'api', 'token');

        $limit = 1;
        $offset = 3;

        $response = $feed->getActivities($offset, $limit);
        $this->assertSame('feed/feed/1/', $response['uri']);

        $response = $feed->getActivities($offset);
        $this->assertSame('feed/feed/1/', $response['uri']);

        $response = $feed->getActivities();
        $this->assertSame('feed/feed/1/', $response['uri']);
    }

    public function testClientRemoveActivity()
    {
        $feed = new BaseFeedStub(null, 'feed', '1', 'api', 'token');
        $aid = '123';
        $response = $feed->removeActivity($aid);
        $this->assertSame('feed/feed/1/123/', $response['uri']);
    }
}
