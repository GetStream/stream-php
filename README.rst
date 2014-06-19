stream-php
=========

.. image:: https://secure.travis-ci.org/tbarbugli/stream-php.png?branch=master
   :target: http://travis-ci.org/tbarbugli/stream-php


stream-php is a PHP client for `Stream <https://getstream.io/>`_.

.. code-block:: php

    // Instantiate a new client
    $client = new GetStream\Stream\Client('YOUR_API_KEY', 'API_KEY_SECRET');
    // Find your API keys here https://getstream.io/dashboard/

    // Instantiate a feed object
    $user_feed_1 = $client->feed('user:1');

    // Get 20 activities starting from activity with id $last_id (fast id offset pagination)
    $results = $user_feed_1->getActivities(0, 20, id_lte=$last_id);

    // Get 10 activities starting from the 5th (slow offset pagination)
    $results = $user_feed_1->getActivities(5, 10);

    // Create a new activity
    $data = [
        "actor_id"=>"1",
        "verb"=>"like",
        "object_id"=>"3"
    ];
    $user_feed_1->addActivity($data);

    // Remove an activity by its id
    $user1->removeActivity('12345678910');

    // Follow another feed
    $user1->followFeed('flat:42');

    // Stop following another feed
    $user1->unfollowFeed('flat:42');


Installation
------------

Install with Composer
~~~~~~~~~~~~~~~~~~~~~

If you're using `Composer <https://getcomposer.org/>`_ to manage
dependencies, you can add Stream with it.

.. code-block:: json

    {
        "require": {
            "get-stream/stream": "$VERSION"
        }
    }

(replace ``$VERSION`` with one of the available versions on `Packagist <https://packagist.org/packages/raven/raven>`_)
or to get the latest version off the master branch:

.. code-block:: json

    {
        "require": {
            "get-stream/stream": "dev-master"
        }
    }

Note that using unstable versions is not recommended and should be avoided.

Composer will take care of the autoloading for you, so if you require the
``vendor/autoload.php``, you're good to go.


Contributing
------------

First, make sure you can run the test suite. Install development dependencies :

::

    $ composer install
    
You may now use phpunit :

::

    $ vendor/bin/phpunit
