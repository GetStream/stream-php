## 5.0.0 - 2021-03-26
* Drop support for PHP 7.2 and add 8.0 support
* Fix undefined constant deprecation from 7.2
* Move to github actions and add static analysis

## 4.1.1 - 2021-01-26
* Fix type of activity_id in remove_activity in docblock

## 4.1.0 - 2020-08-21
* Add kinds filter into getActivities for a feed or a Client
* Fix version header
* Support guzzle 7

## 4.0.1 - 2019-11-25
* PHP 7.4
* Fix for targetFeeds in reactions()->addChild

## 4.0.0 - 2019-11-25
* Upgrade dependencies, drop php5.x support, update tests

## 3.0.2 - 2019-11-12
* Add support for enrichment in getActivities

## 3.0.1 - 2019-07-13
* More flexible collections upsert

## 3.0.0 - 2019-02-11
* Add support for users, collections, reactions, enrichment
* Add `Client::doPartiallyUpdateActivity`
* Add `Client::batchPartialActivityUpdate` methods
* Add `Client::getActivities` methods
* Remove deprecated methods on Feed Class

## 2.9.1 - 2018-12-03
* Added RateLimit methods to StreamFeedException

## 2.9.0 - 2018-10-08
* Added `Client::createUserSessionToken` method

## 2.8.0 - 2018-09-06
* Added unfollow many endpoint.
* Added collection references helpers.

## 2.4.2 - 2017-10-03
* Silently return nothing when meaningless input is given on `Client::updateActivities` method.

## 2.4.1 - 2017-09-26
* Cleaned up test suite and separated integration tests from unit tests in test builds
* Fixed guzzle request options
* Fixed json encoding errors by letting guzzle handle it

## 2.4.0 - 2017-08-31
* Add support for update to target

## 2.3.0 - 2017-03-20
* Add support for activity_copy_limit parameter (follow_many)

## 2.2.9 - 2016-10-15
* Updates to testing, support for more versions

## 2.2.8 - 2016-06-29
* Update to php-jwt 3.0

## 2.0.1 - 2014-11-11
* Simplified syntax to create feeds, follow and unfollow feeds.
* Default HTTP timeout of 3s

## 1.3.4 - 2014-09-08
* Add support for mark read (notifications feeds)
