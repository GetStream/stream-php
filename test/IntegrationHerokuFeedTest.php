<?php
namespace GetStream\Stream;

class HerokuIntegrationTest extends IntegrationTest
{
    protected function setUp()
    {
        putenv('STREAM_URL=https://' . getenv('STREAM_API_KEY') . ':'  .getenv('STREAM_API_SECRET') . '@us-east.getstream.io/?app_id=1');
        $this->client = Client::herokuConnect();
        $this->user1 = $this->client->feed('user', '11');
        $this->aggregated2 = $this->client->feed('aggregated', '22');
        $this->aggregated3 = $this->client->feed('aggregated', '33');
        $this->flat3 = $this->client->feed('flat', '33');
    }

    public function testLegacyURL()
    {
        $client = $this->client = Client::herokuConnect("https://key:secret@getstream.io/?app_id=1");
        $url = $client->buildRequestUrl('x');
        $this->assertSame($url, 'https://api.getstream.io/api/v1.0/x');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage url malformed
     */
    public function testExceptionThrow()
    {
        Client::herokuConnect("https://api_key:@getstream.io/?app_id=1");
    }
}
