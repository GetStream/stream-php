<?php
namespace GetStream\Stream;


class HerokuIntegrationTest extends IntegrationTest
{

    protected function setUp() {
        putenv('STREAM_URL=https://5crf3bhfzesn:tfq2sdqpj9g446sbv653x3aqmgn33hsn8uzdc9jpskaw8mj6vsnhzswuwptuj9su@getstream.io/?site=1');
        $this->client = Client::herokuConnect();
        $this->user1 = $this->client->feed('user:11');
        $this->aggregated2 = $this->client->feed('aggregated:22');
        $this->aggregated3 = $this->client->feed('aggregated:33');
        $this->flat3 = $this->client->feed('flat:33');
    }

}