<?php

namespace GetStream\Integration;

use DateTime;
use DateTimeZone;
use Firebase\JWT\JWT;
use GetStream\Stream\Client;
use GetStream\Stream\Feed;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ReactionTest extends TestCase
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
     * @var Reactions
     */
    protected $reactions;

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
        $this->aggregated2 = $this->client->feed('aggregated', Uuid::uuid4());
        $this->aggregated3 = $this->client->feed('aggregated', Uuid::uuid4());
        $this->flat3 = $this->client->feed('flat', Uuid::uuid4());
        $activity_data = ['actor' => 1, 'verb' => 'tweet', 'object' => 1];
        $response = $this->user1->addActivity($activity_data);
        $this->activity_id = $response['id'];
        $this->reactions = $this->client->reactions();
    }

    public function testSimpleAddReaction()
    {
        $reaction = $this->reactions->add('like', $this->activity_id, 'bob');
        $this->assertSame($reaction['user_id'], 'bob');
        $this->assertSame($reaction['kind'], 'like');
        $this->assertSame($reaction['activity_id'], $this->activity_id);
    }

    public function testAddDataReaction()
    {
        $data = array('client' => 'php');
        $reaction = $this->reactions->add('like', $this->activity_id, 'bob', $data);
        $this->assertSame($reaction['user_id'], 'bob');
        $this->assertSame($reaction['kind'], 'like');
        $this->assertSame($reaction['activity_id'], $this->activity_id);
        $this->assertSame($reaction['data'], $data);
    }

    public function testCreateReference()
    {
        $data = array('client' => 'php');
        $reaction = $this->reactions->add('like', $this->activity_id, 'bob', $data);
        $reactionId = $reaction['id'];
        $refId = $this->reactions->createReference($reaction['id']);
        $this->assertSame($refId, 'SR:' . $reactionId);
        $refObj =  $this->reactions->createReference($reaction['id']);
        $this->assertSame($refObj, 'SR:' . $reactionId);
    }

    public function testAddChildReaction()
    {
        $initial_reaction = $this->reactions->add('like', $this->activity_id, 'bob');
        $child_reaction = $this->reactions->addChild('like', $initial_reaction['id'], 'alice');
        $this->assertSame($child_reaction['user_id'], 'alice');
        $this->assertSame($initial_reaction['user_id'], 'bob');
        $this->assertSame($child_reaction['kind'], 'like');
        $this->assertSame($child_reaction['activity_id'], $this->activity_id);
        $this->assertSame($child_reaction['parent'], $initial_reaction['id']);
    }

    public function testAddTargetFeedsReaction()
    {
        $target_feeds = array($this->aggregated2->getId(), $this->aggregated3->getId());
        $reaction = $this->reactions->add('like', $this->activity_id, 'bob', null, $target_feeds);
        $this->assertSame($reaction['user_id'], 'bob');
        $this->assertSame($reaction['kind'], 'like');
        $this->assertSame($reaction['activity_id'], $this->activity_id);
        $response = $this->aggregated2->getActivities($offset=0, $limit=3);
        // check a targeted feed
        $latest_activity = $response["results"][0]['activities'][0];
        $this->assertSame(
            $latest_activity["reaction"],
            $this->reactions->createReference($reaction)
        );
        $this->assertSame($latest_activity["verb"], "like");
    }

    public function testGetReaction(){
        $created_reaction = $this->reactions->add('like', $this->activity_id, 'bob');
        $retrieved_reaction = $this->reactions->get($created_reaction['id']);
        $this->assertSame($created_reaction['id'], $retrieved_reaction['id']);
        $this->assertSame($created_reaction['user_id'], $retrieved_reaction['user_id']);
        $this->assertSame($created_reaction['kind'], $retrieved_reaction['kind']);
        $this->assertSame($created_reaction['created_at'], $retrieved_reaction['created_at']);
    }

    /**
     * @expectedException \GetStream\Stream\StreamFeedException
     */
    public function testDeleteReaction(){
        $created_reaction = $this->reactions->add('like', $this->activity_id, 'bob');
        $retrieved_reaction = $this->reactions->get($created_reaction['id']);
        $this->reactions->delete($created_reaction['id']);
        $retrieved_reaction = $this->reactions->get($created_reaction['id']);
    }

    public function testUpdateReaction(){
        $data = array('client' => 'php');
        $created_reaction = $this->reactions->add('unlike', $this->activity_id, 'bob', $data);
        $retrieved_reaction = $this->reactions->get($created_reaction['id']);
        $updated_data = array('client' => 'updated-php', 'more' => 'kets');
        $updated_reaction = $this->reactions->update($created_reaction['id'], $updated_data);
        $this->assertSame($retrieved_reaction['data'], $data);
        $this->assertSame($updated_reaction['data'], $updated_data);
    }

    public function testFilterReaction(){
        $reactions = $this->reactions->filter('user_id', 'bob', 'like');
        foreach($reactions['results'] as $reaction){
            $this->assertSame($reaction['kind'], 'like');
            $this->assertSame($reaction['user_id'], 'bob');
        }
        $reactions = $this->reactions->filter('user_id', 'bob', 'unlike');
        foreach($reactions['results'] as $reaction){
            $this->assertSame($reaction['kind'], 'unlike');
            $this->assertSame($reaction['user_id'], 'bob');
        }
    }

}
