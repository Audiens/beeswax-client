# BeesWax API PHP client

[![Build Status](https://travis-ci.org/Audiens/beeswax-client.svg?branch=master)](https://travis-ci.org/Audiens/beeswax-client)
[![Code Coverage](https://scrutinizer-ci.com/g/Audiens/beeswax-client/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Audiens/beeswax-client/?branch=master)
## Implemented features

- [Session login](https://docs.beeswax.com/docs/authentication)
- [Segment create](https://docs.beeswax.com/docs/segment-1)

## How to

### Run the tests

- Copy `.env.dist` to `.env` and edit accordingly. **ATTENTION**: it will create, update, delete data in the sandbox environment.
- Run `vendor/bin/phpunit`

### Use the library

First you need to create a new `BeesWaxSession` object:

```php
<?php
use Audiens\BeesWax\BeesWaxSession;

$session = new BeesWaxSession($buzzKey, $email, $password);
```

- `$buzzKey`: `stinger` for production, `stingersbx` for sandbox.
- `$email`: the user's email
- `$password`: the user's password

Then you can use one of the managers (`BeesWax*Manager`) to access the API. For example using the segment manager:

```php
<?php

use Audiens\BeesWax\Segment\BeesWaxSegmentManager;

$session = /**/;
$segmentManager = new BeesWaxSegmentManager($sesion);

// ...
$segmentManager->create($mySegment);
```

### Error handling

All the exceptions generated in this library extend `Audiens\BeesWax\Exception\BeesWaxGenericException`.

Particular exceptions may rise, such as `BeesWaxLoginException` or `BeesWaxResponseException`, accordingly to the PHPDoc
documentation.
