<?php

namespace GetStream\Integration;

use GetStream\Stream\Client;
use GetStream\Stream\Feed;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
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
     * @var Users
     */
    protected $users;

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
        $this->users = $this->client->users();

        // $this->user1 = $this->client->feed('user', $this->generateGuid());
    }

    private function generateGuid()
    {
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));
    }

    public function testSimpleAddUser()
    {
        $uuid = $this->generateGuid()->toString();
        $user = $this->users->add($uuid);
        $this->assertSame($user['id'], $uuid);
        $this->assertTrue(array_key_exists('created_at', $user));
        $this->assertTrue(array_key_exists('updated_at', $user));
        $this->assertTrue(array_key_exists('data', $user));
    }

    public function testGetOrCreateUser()
    {
        $uuid = $this->generateGuid()->toString();
        $user1 = $this->users->add($uuid);
        $user2 = $this->users->add($uuid, null, true);

        $this->assertSame($user1['id'], $user2['id']);
        $this->assertSame($user1['created_at'], $user2['created_at']);
        $this->assertSame($user1['updated_at'], $user2['updated_at']);
    }

    public function testAddUserData()
    {
        $data = ['client' => 'php'];
        $uuid = $this->generateGuid()->toString();
        $user = $this->users->add($uuid, $data);
        $this->assertSame($user['id'], $uuid);
        $this->assertTrue(array_key_exists('created_at', $user));
        $this->assertTrue(array_key_exists('updated_at', $user));
        $this->assertSame($user['data'], $data);
    }

    public function testGetUser()
    {
        $uuid = $this->generateGuid()->toString();
        $created_user = $this->users->add($uuid);
        $retrieved_user = $this->users->get($uuid);
        $this->assertSame($created_user['id'], $retrieved_user['id']);
        $this->assertSame($created_user['updated_at'], $retrieved_user['updated_at']);
        $this->assertSame($created_user['created_at'], $retrieved_user['created_at']);
        $this->assertSame($created_user['data'], $retrieved_user['data']);
        $this->assertSame($created_user['data'], []);
    }

    public function testDeleteUser()
    {
        $this->expectException(\GetStream\Stream\StreamFeedException::class);
        $uuid = $this->generateGuid()->toString();
        $created_user = $this->users->add($uuid);
        $retrieved_user = $this->users->get($created_user['id']);
        $this->users->delete($created_user['id']);
        $retrieved_user = $this->users->get($created_user['id']);
    }

    public function testUpdateUser()
    {
        $uuid = $this->generateGuid()->toString();
        $data = ['client' => 'php'];
        $created_user = $this->users->add($uuid, $data);
        $retrieved_user = $this->users->get($created_user['id']);
        $updated_data = ['client' => 'updated-php', 'more' => 'keys'];
        $updated_user = $this->users->update($created_user['id'], $updated_data);
        $this->assertSame($retrieved_user['data'], $data);
        $this->assertSame($updated_user['data'], $updated_data);
    }
}
