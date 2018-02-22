<?php

namespace GetStream\Unit;

use GetStream\Stream\Client;
use GetStream\Stubs\BaseFeedStub;
use PHPUnit\Framework\TestCase;

class BaseFeedTest extends TestCase
{
    public function testClientFeedAddActivity()
    {
        $client = $this->createClientMock();
        $feed = new BaseFeedStub($client, 'feed', '1', 'api', 'token');
        $data = ['name' => 'php client'];
        $response = $feed->addActivity($data);
        $this->assertSame('feed/feed/1/', $response['uri']);
    }

    /**
     * @expectedException \GetStream\Stream\StreamFeedException
     */
    public function testValidateSlug()
    {
        $client = $this->createClientMock();
        new BaseFeedStub($client, 'feed-ko', '1', 'api', 'token');
    }

    /**
     * @expectedException \GetStream\Stream\StreamFeedException
     */
    public function testValidateUserId()
    {
        $client = $this->createClientMock();
        new BaseFeedStub($client, 'feed_ko', 'ko:1', 'api', 'token');
    }

    public function testDashIsOkUserId()
    {
        $client = $this->createClientMock();
        new BaseFeedStub($client, 'feed_ko', 'ko-1', 'api', 'token');
    }

    public function testGetToken()
    {
        $client = $this->createClientMock();
        $feed = new BaseFeedStub($client, 'feed', '1', 'api', 'token');
        $this->assertSame('token', $feed->getToken());
    }

    public function testClientFeedGetActivities()
    {
        $client = $this->createClientMock();
        $feed = new BaseFeedStub($client, 'feed', '1', 'api', 'token');

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
        $client = $this->createClientMock();
        $feed = new BaseFeedStub($client, 'feed', '1', 'api', 'token');
        $aid = '123';
        $response = $feed->removeActivity($aid);
        $this->assertSame('feed/feed/1/123/', $response['uri']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\GetStream\Stream\Client
     */
    private function createClientMock()
    {
        return $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
    }
}
