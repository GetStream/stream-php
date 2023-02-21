# Changelog

All notable changes to this project will be documented in this file. See [standard-version](https://github.com/conventional-changelog/standard-version) for commit guidelines.

## [7.0.0](https://github.com/GetStream/stream-php/compare/6.0.0...7.0.0) (2023-02-21)

## [6.0.0](https://github.com/GetStream/stream-php/compare/5.2.0...6.0.0) (2022-08-29)


### Features

* drop php 7.3 support ([#117](https://github.com/GetStream/stream-php/issues/117)) ([f97432b](https://github.com/GetStream/stream-php/commit/f97432bfafb9adfa963ef3254a6e2575fb4d7b01))

## [5.2.0](https://github.com/GetStream/stream-php/compare/5.1.1...5.2.0) (2022-08-29)


### Features

* **activities:** add get activities api ([#111](https://github.com/GetStream/stream-php/issues/111)) ([971f6a6](https://github.com/GetStream/stream-php/commit/971f6a6135fd591603278289d01a0e3962785095))
* **guzzle:** add custom http middleware possibility ([#112](https://github.com/GetStream/stream-php/issues/112)) ([6960b3f](https://github.com/GetStream/stream-php/commit/6960b3f9b67170be845404d87c0ffc2e48237a46))


### Bug Fixes

* bump guzzle for security and add 8.2 ([#114](https://github.com/GetStream/stream-php/issues/114)) ([31bc4f8](https://github.com/GetStream/stream-php/commit/31bc4f80740bb0192d8d4a14b23837bcf11a4d4d))
* fix phan errors ([1b15faa](https://github.com/GetStream/stream-php/commit/1b15faa24c9f29d289f8565fff13cced6e31edcf))
* pr comment fixes ([3bd65f3](https://github.com/GetStream/stream-php/commit/3bd65f32c714c39114a9e7cbc52adf14b9f06acc))

## 5.1.1 - 2021-09-28
* Replace deprecated query parse of Guzzle

## 5.1.0 - 2021-06-21
* Add target feeds extra data support for reactions

## 5.0.2 - 2021-06-08
* Accept user_id for own reactions
* Handle deprecated cs fixer config

## 5.0.1 - 2021-04-08
* Fix namespacing issue for constant initialization of version

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
