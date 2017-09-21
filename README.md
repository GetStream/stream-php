stream-php
==========

[![Build Status](https://travis-ci.org/GetStream/stream-php.svg?branch=master)](https://travis-ci.org/GetStream/stream-php)
[![Coverage Status](https://coveralls.io/repos/github/GetStream/stream-php/badge.svg?branch=master)](https://coveralls.io/github/GetStream/stream-php?branch=master)
[![Latest Stable Version](https://poser.pugx.org/get-stream/stream/v/stable)](https://packagist.org/packages/get-stream/stream)

[stream-php](https://github.com/GetStream/stream-php) is the official PHP client for [Stream](https://getstream.io/), a web service for building scalable newsfeeds and activity streams.

You can sign up for a Stream account at https://getstream.io/get_started.

### Installation

#### Composer

```
composer require get-stream/stream
```

Composer will install our latest version automatically.

#### PHP compatibility

Current releases require PHP `>5.5` and depend on Guzzle version 6.

If you need to use the client with PHP 5.4 or earlier versions of Guzzle, you can grab an earlier version of this package:

```
composer require get-stream/stream:"~2.1.0"
```

### Full documentatation

Our full documentation for this package is available at [https://getstream.io/docs/php/](https://getstream.io/docs/php/).

### Quick start

First, [signup here](https://getstream.io/dashboard/) for a free account and grab your API key and secret.

```php
// Instantiate a new client, find your API keys in the dashboard.
$client = new GetStream\Stream\Client('YOUR_API_KEY', 'YOUR_API_SECRET');

// Instantiate a feed object
$user_feed_1 = $client->feed('user', '1');

// Get 20 activities starting from activity with id $last_id (fast id offset pagination)
$results = $user_feed_1->getActivities(0, 20, $last_id);

// Get 10 activities starting from the 5th (slow offset pagination)
$results = $user_feed_1->getActivities(5, 10);

// Create a new activity
$data = [
    "actor"=>"1",
    "verb"=>"like",
    "object"=>"3",
    "foreign_id"=>"post:42"
];
$user_feed_1->addActivity($data);
// Create a bit more complex activity
$now = new \DateTime("now", new \DateTimeZone('Pacific/Nauru'));
$data = ['actor' => 1, 'verb' => 'run', 'object' => 1, 'foreign_id' => 'run:1',
	'course' => ['name'=> 'Golden Gate park', 'distance'=> 10],
	'participants' => ['Thierry', 'Tommaso'],
	'started_at' => $now
];
$user_feed_1->addActivity($data);

// Remove an activity by its id
$user_feed_1->removeActivity("e561de8f-00f1-11e4-b400-0cc47a024be0");

// Remove activities by their foreign_id
$user_feed_1->removeActivity('post:42', true);

// Let user 1 start following user 42's flat feed
$user_feed_1->followFeed('flat', '42');

// Let user 1 start following user 42's flat feed but only copy 10 activities to target feed
$user_feed_1->followFeed('flat', '42', 10);

// Let user 1 stop following user 42's flat feed
$user_feed_1->unfollowFeed('flat', '42');

// Let user 1 stop following user 42's flat feed but keep the history in its feed
$user_feed_1->unfollowFeed('flat', '42', true);

// Batch adding activities
$activities = array(
    array('actor' => '1', 'verb' => 'tweet', 'object' => '1'),
    array('actor' => '2', 'verb' => 'like', 'object' => '3')
);
$user_feed_1->addActivities($activities);

// Add an activity and push it to other feeds too using the `to` field
$data = [
    "actor"=>"1",
    "verb"=>"like",
    "object"=>"3",
    "to"=>["user:44", "user:45"]
];
$user_feed_1->addActivity($data);

// Delete a feed (and its content)
$user_feed_1->delete();

// Generating tokens for client side usage
$token = $user_feed_1->getToken();

// Javascript client side feed initialization
// user1 = client.feed('user', '1', "$token");

// Generating read-only tokens for client side usage
$readonlyToken = $user_feed_1->getReadonlyToken();

// Javascript client side feed initialization (readonly)
// user1 = client.feed('user', '1', "$readonlyToken");

// Retrieve first 10 followers of a feed
$user_feed_1->followers(0, 10);

// Retrieve 2 to 10 followers
$user_feed_1->followers(2, 10);

// Retrieve 10 feeds followed by $user_feed_1
$user_feed_1->following(0, 10);

// Retrieve 10 feeds followed by $user_feed_1 starting from the 10th (2nd page)
$user_feed_1->following(10, 20);

// Check if $user_feed_1 follows specific feeds
$user_feed_1->following(0, 2, ['user:42', 'user:43']);

// Batch operations (batch activity add, batch follow)
$batcher = $client->batcher();

// Add one activity to many feeds
$activity = array('actor' => '1', 'verb' => 'tweet', 'object' => '1');
$feeds = ['flat:user1', 'flat:user2'];
$batcher->addToMany($activity, $feeds);

// Create many follow
$follows = [
    ['source' => 'flat:b1', 'target' => 'user:b1'],
    ['source' => 'flat:b1', 'target' => 'user:b3']
];
$batcher->followMany($follows);

// Create many follows without copying activities
$batcher->followMany($follows, 0);
```

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

Copyright (c) 2014-2017 Stream.io Inc, and individual contributors. All rights reserved.

See the file "LICENSE" for information on the history of this software, terms & conditions for usage, and a DISCLAIMER OF ALL WARRANTIES.
