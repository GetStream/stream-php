<?php
namespace GetStream\Stream;


class IntegrationTest extends \PHPUnit_Framework_TestCase
{

    protected $client;
    protected $user1;
    protected $aggregated2;
    protected $aggregated3;
    protected $flat3;

    protected function setUp() {
        $this->client = new Client('ahj2ndz7gsan', 'gthc2t9gh7pzq52f6cky8w4r4up9dr6rju9w3fjgmkv6cdvvav2ufe5fv7e2r9qy');
        $this->user1 = $this->client->feed('user:11');
        $this->aggregated2 = $this->client->feed('aggregated:22');
        $this->aggregated3 = $this->client->feed('aggregated:33');
        $this->flat3 = $this->client->feed('flat:33');
    }

    public function testAddActivity() {
        $activity_data = array('actor'=> 1, 'verb'=> 'tweet', 'object'=> 1);
        $response = $this->user1->addActivity($activity_data);
        $activity_id = $response['id'];
        $activities = $this->user1->getActivities(0, 1)['results'];
        $this->assertSame(count($activities), 1);
        $this->assertSame($activities[0]['id'], $activity_id);
    }

    public function testAddActivityWithArray() {
        $complex = array('tommaso', 'thierry');
        $activity_data = array('actor'=> 1, 'verb'=> 'tweet', 'object'=> 1, 'complex'=>$complex);
        $response = $this->user1->addActivity($activity_data);
        $activity_id = $response['id'];
        $activities = $this->user1->getActivities(0, 1)['results'];
        $this->assertSame(count($activities), 1);
        $this->assertSame($activities[0]['id'], $activity_id);
        sort($activities[0]['complex']);
        sort($complex);
        $this->assertSame($activities[0]['complex'], $complex);
    }

    public function testAddActivityWithAssocArray() {
        $complex = array('author' => 'tommaso', 'bcc' => 'thierry');
        $activity_data = array('actor'=> 1, 'verb'=> 'tweet', 'object'=> 1, 'complex'=>$complex);
        $response = $this->user1->addActivity($activity_data);
        $activity_id = $response['id'];
        $activities = $this->user1->getActivities(0, 1)['results'];
        $this->assertSame(count($activities), 1);
        $this->assertSame($activities[0]['id'], $activity_id);
        sort($activities[0]['complex']);
        sort($complex);
        $this->assertSame($activities[0]['complex'], $complex);
    }

    public function testRemoveActivity() {
        $activity_data = array('actor'=> 1, 'verb'=> 'tweet', 'object'=> 1);
        $response = $this->user1->addActivity($activity_data);
        $activity_id = $response['id'];
        $activities = $this->user1->getActivities(0, 1)['results'];
        $this->assertSame(count($activities), 1);
        $this->assertSame($activities[0]['id'], $activity_id);
        $this->user1->removeActivity($activity_id);
        $activities = $this->user1->getActivities(0, 1)['results'];
        $this->assertNotSame($activities[0]['id'], $activity_id);
    }

    public function testRemoveActivityByForeignId() {
        $fid = 'post:42';
        $activity_data = array('actor'=> 1, 'verb'=> 'tweet', 'object'=> 1, 'foreign_id'=> $fid);
        $response = $this->user1->addActivity($activity_data);
        $activity_id = $response['id'];
        $activities = $this->user1->getActivities(0, 1)['results'];
        $this->assertSame(count($activities), 1);
        $this->assertSame($activities[0]['id'], $activity_id);
        $this->assertSame($activities[0]['foreign_id'], $fid);
        $this->user1->removeActivity($fid, true);
        $activities = $this->user1->getActivities(0, 1)['results'];
        $this->assertNotSame($activities[0]['id'], $activity_id);
    }

    public function testException() {
        $activity_data = array('actor'=> 1, 'verb'=> 'tweet', 'object'=> 1, 'new_field' => '42');
        $response = $this->user1->addActivity($activity_data);
        $activities = $this->user1->getActivities(0, 1)['results'];
        $this->assertNotSame($activities[0]['new_field'], 42);
    }

    public function testFlatFollowUnfollow() {
        $this->user1->unfollowFeed('flat:33');
        sleep(3);
        $activity_data = array('actor'=> 1, 'verb'=> 'tweet', 'object'=> 1);
        $response = $this->flat3->addActivity($activity_data);
        $activity_id = $response['id'];
        $this->user1->followFeed('flat:33');
        sleep(5);
        $activities = $this->user1->getActivities(0, 1)['results'];
        $this->assertSame(count($activities), 1);
        $this->assertSame($activities[0]['id'], $activity_id);
        $this->user1->unfollowFeed('flat:33');
        sleep(5);
        $activities = $this->user1->getActivities(0, 1)['results'];
        $this->assertNotSame($activities[0]['id'], $activity_id);
    }

    public function testDelete() {
        $activity_data = array('actor'=> 1, 'verb'=> 'tweet', 'object'=> 1);
        $this->user1->addActivity($activity_data);
        $activities = $this->user1->getActivities(0,1)['results'];
        $this->assertSame(count($activities), 1);
        $this->user1->delete();
        $activities = $this->user1->getActivities(0,1)['results'];
        $this->assertSame(count($activities), 0);
    }

    public function testGet() {
        $activity_data = array('actor'=> 1, 'verb'=> 'tweet', 'object'=> 1);
        $first_id = $this->user1->addActivity($activity_data)['id'];

        $activity_data = array('actor'=> 1, 'verb'=> 'tweet', 'object'=> 2);
        $second_id = $this->user1->addActivity($activity_data)['id'];

        $activity_data = array('actor'=> 1, 'verb'=> 'tweet', 'object'=> 3);
        $third_id = $this->user1->addActivity($activity_data)['id'];

        $activities = $this->user1->getActivities(0, 2)['results'];
        $this->assertSame(count($activities), 2);
        $this->assertSame($activities[0]['id'], $third_id);
        $this->assertSame($activities[1]['id'], $second_id);

        $activities = $this->user1->getActivities(1, 2)['results'];
        $this->assertSame($activities[0]['id'], $second_id);

        $id_offset =  array('id_lt'=>$third_id);
        $activities = $this->user1->getActivities(0, 2, $id_offset)['results'];
        $this->assertSame($activities[0]['id'], $second_id);

    }

}