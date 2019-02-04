<?php

namespace GetStream\Integration;

use DateTime;
use DateTimeZone;
use Firebase\JWT\JWT;
use GetStream\Stream\Client;
use GetStream\Stream\Feed;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

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

    protected function setUp()
    {
        $this->client = new Client(
            getenv('STREAM_API_KEY'),
            getenv('STREAM_API_SECRET'),
            'v1.0',
            getenv('STREAM_REGION')
        );
        $this->client->setLocation('qa');
        $this->client->timeout = 10000;
        $this->user1 = $this->client->feed('user', Uuid::uuid4());
        $this->user2 = $this->client->feed('user', Uuid::uuid4());
        // $this->aggregated2 = $this->client->feed('aggregated', Uuid::uuid4());
        // $this->aggregated3 = $this->client->feed('aggregated', Uuid::uuid4());
        // $this->flat3 = $this->client->feed('flat', Uuid::uuid4());
        // $activity_data = ['actor' => 1, 'verb' => 'tweet', 'object' => 1];
        // $response = $this->user1->addActivity($activity_data);
        // $this->activity_id = $response['id'];
        $this->collections = $this->client->collections();
    }

    public function testUpsert()
    {
        $collection = $this->collections->upsert('animals', ['id' => '1', 'name' => 'bear', 'color' => 'blue']);
        $collection = $this->collections->upsert('items', ['id' => '42', 'name' => 'towel']);

    }

    public function testAddDataCollection()
    {
        $data = array('client' => 'php');
        $collection = $this->collections->add('like', $this->activity_id, 'bob', $data);
        $this->assertSame($collection['user_id'], 'bob');
        $this->assertSame($collection['kind'], 'like');
        $this->assertSame($collection['activity_id'], $this->activity_id);
        $this->assertSame($collection['data'], $data);
    }

    public function testCreateReference()
    {
        $data = array('client' => 'php');
        $collection = $this->collections->add('like', $this->activity_id, 'bob', $data);
        $collectionId = $collection['id'];
        $refId = $this->collections->createReference($collection['id']);
        $this->assertSame($refId, 'SR:' . $collectionId);
        $refObj =  $this->collections->createReference($collection);
        $this->assertSame($refObj, 'SR:' . $collectionId);
    }

    public function testAddChildCollection()
    {
        $initial_collection = $this->collections->add('like', $this->activity_id, 'bob');
        $child_collection = $this->collections->addChild('like', $initial_collection['id'], 'alice');
        $this->assertSame($child_collection['user_id'], 'alice');
        $this->assertSame($initial_collection['user_id'], 'bob');
        $this->assertSame($child_collection['kind'], 'like');
        $this->assertSame($child_collection['activity_id'], $this->activity_id);
        $this->assertSame($child_collection['parent'], $initial_collection['id']);
    }

    public function testAddTargetFeedsCollection()
    {
        $target_feeds = array($this->aggregated2->getId(), $this->aggregated3->getId());
        $collection = $this->collections->add('like', $this->activity_id, 'bob', null, $target_feeds);
        $this->assertSame($collection['user_id'], 'bob');
        $this->assertSame($collection['kind'], 'like');
        $this->assertSame($collection['activity_id'], $this->activity_id);
        $response = $this->aggregated2->getActivities($offset=0, $limit=3);
        // check a targeted feed
        $latest_activity = $response["results"][0]['activities'][0];
        $this->assertSame(
            $latest_activity["collection"],
            $this->collections->createReference($collection)
        );
        $this->assertSame($latest_activity["verb"], "like");
    }

    public function testGetCollection(){
        $created_collection = $this->collections->add('like', $this->activity_id, 'bob');
        $retrieved_collection = $this->collections->get($created_collection['id']);
        $this->assertSame($created_collection['id'], $retrieved_collection['id']);
        $this->assertSame($created_collection['user_id'], $retrieved_collection['user_id']);
        $this->assertSame($created_collection['kind'], $retrieved_collection['kind']);
        $this->assertSame($created_collection['created_at'], $retrieved_collection['created_at']);
    }

    /**
     * @expectedException \GetStream\Stream\StreamFeedException
     */
    public function testDeleteCollection(){
        $created_collection = $this->collections->add('like', $this->activity_id, 'bob');
        $retrieved_collection = $this->collections->get($created_collection['id']);
        $this->collections->delete($created_collection['id']);
        $retrieved_collection = $this->collections->get($created_collection['id']);
    }

    public function testUpdateCollection(){
        $data = array('client' => 'php');
        $created_collection = $this->collections->add('unlike', $this->activity_id, 'bob', $data);
        $retrieved_collection = $this->collections->get($created_collection['id']);
        $updated_data = array('client' => 'updated-php', 'more' => 'kets');
        $updated_collection = $this->collections->update($created_collection['id'], $updated_data);
        $this->assertSame($retrieved_collection['data'], $data);
        $this->assertSame($updated_collection['data'], $updated_data);
    }

    public function testFilterCollection(){
        $collections = $this->collections->filter('user_id', 'bob', 'like');
        foreach($collections['results'] as $collection){
            $this->assertSame($collection['kind'], 'like');
            $this->assertSame($collection['user_id'], 'bob');
        }
        $collections = $this->collections->filter('user_id', 'bob', 'unlike');
        foreach($collections['results'] as $collection){
            $this->assertSame($collection['kind'], 'unlike');
            $this->assertSame($collection['user_id'], 'bob');
        }
    }

}
