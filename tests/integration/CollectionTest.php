<?php

namespace GetStream\Integration;

use DateTime;
use DateTimeZone;
use Firebase\JWT\JWT;
use GetStream\Stream\Client;
use GetStream\Stream\Feed;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Exception\ClientException;

class CollectionTest extends TestCase
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Feed
     */
    protected $user1;

    /**
     * @var Feed
     */
    protected $user2;

    /**
     * @var Feed
     */
    protected $aggregated2;

    /**
     * @var Feed
     */
    protected $aggregated3;

    /**
     * @var Feed
     */
    protected $flat3;

    /**
     * @var string
     */
    protected $activity_id;

    /**
     * @var Collections
     */
    protected $collections;

    protected function setUp():void
    {
        $this->client = new Client(
            getenv('STREAM_API_KEY'),
            getenv('STREAM_API_SECRET'),
            'v1.0',
            getenv('STREAM_REGION')
        );
        $this->client->setLocation('qa');
        $this->client->timeout = 10000;
        $this->user1 = $this->client->feed('user', $this->generateGuid());
        $this->user2 = $this->client->feed('user', $this->generateGuid());
        $this->collections = $this->client->collections();
    }

    public function cleanUp()
    {
        try {
            $this->collections->delete("food", "cheese-burger");
        } catch (ClientException $e) {
            // pass
        }
    }

    private function generateGuid()
    {
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));
    }

    public function testUpsert()
    {
        $collection = $this->collections->upsert('animals', [['id' => '1', 'name' => 'bear', 'color' => 'blue']]);
        $this->assertSame($collection['data']['animals'][0]['name'], 'bear');
        $collection = $this->collections->upsert('items', [['id' => '42', 'name' => 'towel']]);
        $this->assertSame($collection['data']['items'][0]['name'], 'towel');
    }

    public function testAddCollection()
    {
        $response = $this->collections->add(
            "food",
            ["name" => "Cheese Burger", "rating" => "4 stars"]
        );
        $this->assertNotSame($response["id"], "cheese-burger");
        $this->assertSame($response["collection"], 'food');
        $this->assertSame($response["foreign_id"], "food:" . $response['id']);
        $this->assertSame($response['data']['name'], 'Cheese Burger');
    }

    public function testAddCollectionWithId()
    {
        $response = $this->collections->add(
            "food",
            ["name" => "Cheese Burger", "rating" => "4 stars"],
            "cheese-burger"
        );
        $this->assertSame($response["id"], "cheese-burger");
        $this->assertSame($response["collection"], "food");
        $this->assertSame($response["foreign_id"], "food:cheese-burger");
        $this->assertSame($response['data']['name'], 'Cheese Burger');
    }

    public function testAddCollectionAgain()
    {
        // Adding again should throw error
        $this->expectException(\GetStream\Stream\StreamFeedException::class);
        $response = $this->collections->add(
            "food",
            ["name" => "Cheese Burger", "rating" => "4 stars"],
            "cheese-burger"
        );
    }

    public function testDeleteCollection()
    {
        $response = $this->collections->delete("food", "cheese-burger");
        $this->assertTrue(array_key_exists("duration", $response));
    }

    public function testDeleteCollectionAgain()
    {
        $this->expectException(\GetStream\Stream\StreamFeedException::class);
        $response = $this->collections->delete("food", "cheese-burger");
    }

    public function testCreateReference()
    {
        $refId = $this->collections->createReference("item", "42");
        $this->assertSame($refId, 'SO:item:42');
    }

    public function testGetCollection()
    {
        $created_collection = $this->collections->add(
            "food",
            ["name" => "Cheese Burger", "rating" => "4 stars"]
        );
        $retrieved_collection = $this->collections->get('food', $created_collection['id']);
        $this->assertSame($created_collection['id'], $retrieved_collection['id']);
        $this->assertSame($created_collection['data']['name'], $retrieved_collection['data']['name']);
        $this->assertSame($created_collection['collection'], $retrieved_collection['collection']);
        $this->assertSame($created_collection['created_at'], $retrieved_collection['created_at']);
    }

    public function testUpdateCollection()
    {
        $created_collection = $this->collections->add(
            "food",
            ["name" => "Cheese Burger", "rating" => "4 stars"]
        );
        $response = $this->collections->update("food", $created_collection['id'], ["name" => "Cheese Burger", "rating" => "1 stars"]);
        $this->assertSame($response['data']['rating'], '1 stars');
        $this->assertSame($response['data']['name'], 'Cheese Burger');
    }

    public function testFilterCollection()
    {
        $created_collection = $this->collections->add(
            "food",
            ["name" => "Cheese Burger", "rating" => "4 stars"],
            'cheese-burger'
        );
        $response = $this->collections->select('food', ['cheese-burger', '124']);
        $this->assertSame($response['response']['data'][0]['id'], 'cheese-burger');
        $response = $this->collections->delete("food", "cheese-burger");
    }

    public function testDeleteManyCollection()
    {
        // Adding again should throw error
        $response = $this->collections->add(
            "food",
            ["name" => "Cheese Burger", "rating" => "4 stars"],
            "cheese-burger-1"
        );
        $response = $this->collections->add(
            "food",
            ["name" => "Cheese Burger", "rating" => "4 stars"],
            "cheese-burger-2"
        );
        $response = $this->collections->deleteMany("food", ["cheese-burger-1", "cheese-burger-2"]);
        $this->assertTrue(array_key_exists("duration", $response));
    }
}
