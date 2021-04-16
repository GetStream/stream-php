# stream-php

[![build](https://github.com/GetStream/stream-php/workflows/build/badge.svg)](https://github.com/GetStream/stream-php/actions) [![Latest Stable Version](https://poser.pugx.org/get-stream/stream/v/stable)](https://packagist.org/packages/get-stream/stream)

[stream-php](https://github.com/GetStream/stream-php) is the official PHP client for [Stream](https://getstream.io/), a web service for building scalable newsfeeds and activity streams.

Note that there is also a higher level [Laravel](https://github.com/getstream/stream-laravel) integration which hooks into the Eloquent ORM.

You can sign up for a Stream account at https://getstream.io/get_started.

### Installation

#### Composer

```
composer require get-stream/stream
```

Composer will install our latest version automatically.

#### PHP compatibility

Current releases require PHP `7.3` or higher, and depends on Guzzle version `^6.3.3`.

If you need to use the client with an old PHP or earlier versions of Guzzle, you can grab an earlier version of this package, for example:

```
composer require get-stream/stream:"~2.1.0"
```

See the [action](.github/workflows/ci.yml) for details of how it is built and tested against different PHP versions.

### Documentation

Our full documentation for this package is available at [https://getstream.io/docs/php/](https://getstream.io/docs/php/).

#### Quick start

First, [signup here](https://getstream.io/dashboard/) for a free account and grab your API key and secret.

Initiating a Client and a Feed object:

```php
// Instantiate a new client, find your API keys in the dashboard.
$client = new GetStream\Stream\Client('YOUR_API_KEY', 'YOUR_API_SECRET');

// Instantiate a feed object
$userFeed = $client->feed('user', '1');
```

By default, the Client will target the GetStream REST API at `stream-io-api.com/api`.
If you want to change this for some reason you can set the `STREAM_BASE_URL` environment variable.

Activities in a feed:

```php
// Create a new activity
$data = [
    'actor' => '1',
    'verb' => 'like',
    'object' => '3',
    'foreign_id' => 'like:42',
];

$response = $userFeed->addActivity($data);

// The response will include Stream's internal ID:
// {"id": "e561...", "actor": "1", ...}

// Get the latest activities for this user's personal feed, based on who they are following.
$response = $userFeed->getActivities();

// The response will be the json decoded API response.
// {"duration": 45ms, "next": "/api/v1.0/feed/...", "results": [...]}

// Remove an activity by its ID
$userFeed->removeActivity('e561de8f-00f1-11e4-b400-0cc47a024be0');

// To remove activities by their foreign_id, set the "foreign id" flag to true.
$userFeed->removeActivity('like:42', true);
```

Following/follower relations of a feed:

```php
// When user 1 starts following user 37's activities
$userFeed->follow('user', '37');

// When user 1 stops following user 37's activities
$userFeed->unfollow('user', '37');

// Retrieve followers of a feed
$userFeed->followers();

// Retrieve feeds followed by $userFeed
$userFeed->following();
```

Advanced activity operations:

```php
// Create a bit more complex activity with custom fields
$data = [
    'actor' => 1,
    'verb' => 'run',
    'object' => 1,
    'foreign_id' => 'run:42',

    // Custom fields:
    'course' => [
        'name'=> 'Golden Gate park',
        'distance'=> 10,
    ],
    'participants' => ['Thierry', 'Tommaso'],
    'started_at' => new DateTime('now', new DateTimeZone('Pacific/Nauru'),
];

// Add an activity and push it to other feeds too using the `to` field
$data = [
    'actor' => '1',
    'verb' => 'like',
    'object' => '3',
    'to' => [
        'user:44',
        'user:45',
    ],
];

$userFeed->addActivity($data);

// Batch adding activities
$activities = [
    ['actor' => '1', 'verb' => 'tweet', 'object' => '1'],
    ['actor' => '2', 'verb' => 'like', 'object' => '3'],
];

$userFeed->addActivities($activities);

// Delete an entire feed and its content
$userFeed->delete();
```

Advanced batching:

```php
// Batch operations (batch activity add, batch follow)
$batcher = $client->batcher();

// Add one activity to many feeds
$activity = ['actor' => '1', 'verb' => 'tweet', 'object' => '1'];
$feeds = ['user:1', 'user:2'];

$batcher->addToMany($activity, $feeds);

// Create many follow relations
$follows = [
    ['source' => 'user:b1', 'target' => 'user:b2'],
    ['source' => 'user:b1', 'target' => 'user:b3'],
];

$batcher->followMany($follows);
```

Generating tokens for client-side usage:

```php
// Generating a user token
$userToken = $client->createUserSessionToken("the-user-id");
```

RateLimits:

If your app hits a ratelimit, a StreamFeedException is thrown. You can
get additional info by catching the exception and calling the
following methods:

```php

try {
    $client->updateActivity($activity);
}catch(StreamFeedException $e){
    $limit = $e->getRateLimitLimit();
    $remaining = $e->getRateLimitRemaining();
    $reset = $e->getRateLimitReset(); // a unix timestamp
}
```

Reactions:

The reactions module has the following methods.

    - add(string $kind, string $activityId, string $userId, array $data=null, array $targetFeeds=null)
    - addChild(string $kind, string $parentId, string $userId, array $data=null, array $targetFeeds=null)
    - delete(string $reactionId)
    - filter(string $lookupField, string $lookupValue, string $kind=null, array $params=null)
    - get(string $reactionId)
    - update(string $reactionId, array $data=null, array $targetFeeds=null)

Also see documention on the [reactions endpoint](https://getstream.io/docs_rest/#reactions)

```php

$reaction = $client->reactions()->add('like', $activity_id, 'bob');

$bob_likes = $client->reactions()->filter('user_id', 'bob', 'like');

$client->reactions()->delete($reaction['id']);

```

Users:

The users module has the following methods.

    - add(string $userId, array $data=null, bool $getOrCreate=false)
    - createReference(string $userId)
    - delete(string $userId)
    - get(string $userId)
    - update(string $userId, array $data=null)

Also see documention on the [users endpoint](https://getstream.io/docs/php/#users_introduction)

```php

$user = $client->users()->add('42');

$user =  $client->users()->update('42', array('name' => 'Arthur Dent');

$client->users()->delete('42');

```

Again, our full documentation with all options and methods, is available at [https://getstream.io/docs/php/](https://getstream.io/docs/php/).

### Framework integration

#### Laravel

There's a higher level integration with [Laravel](https://laravel.com) called [`get-stream/stream-laravel`](https://github.com/getstream/stream-laravel).
The `stream-laravel` integration helps you to hook into the Laravel's Eloquent ORM to sync data to Stream.

### Contributing

We love contributions. We love contributions with tests even more! To run the test-suite to ensure everything still works, run phpunit:

```
vendor/bin/phpunit --testsuite "Unit Test Suite"
```

### Copyright and License Information

Project is licensed under the [BSD 3-Clause](LICENSE).
