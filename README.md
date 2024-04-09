# Test traits

This is a clone from [selective/test-traits](https://github.com/selective-php/test-traits) containing additional useful
test traits, including a proper [fixture trait](#fixturetesttrait) for integration testing.

[![Latest Version on Packagist](https://img.shields.io/github/release/samuelgfeller/test-traits.svg)](https://packagist.org/packages/samuelgfeller/test-traits)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)
[![Build Status](https://github.com/samuelgfeller/test-traits/workflows/build/badge.svg)](https://github.com/samuelgfeller/test-traits/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/samuelgfeller/test-traits.svg)](https://packagist.org/packages/samuelgfeller/test-traits/stats)

## Requirements

* PHP 8.1+

## Installation

```bash
composer require samuelgfeller/test-traits --dev
```

## Traits documentation

* [FixtureTestTrait](#FixtureTestTrait)
* [HttpTestTrait](#HttpTestTrait)
* [RouteTestTrait](#RouteTestTrait)
* [MailerTestTrait](#MailerTestTrait)

## FixtureTestTrait

A trait designed to create and insert fixtures with data that can be defined in the test function.

**Provided method**

```php
protected function insertFixture(FixtureInterface $fixture, array $attributes = []): array`
```

**Usage**

```php
// Inserts the fixture with the first_name being "Bob" and the rest default values from the fixture.
// Returns the inserted row data with the auto-incremented id.
$bobUserRow = $this->insertFixture(new UserFixture(), ['first_name' => 'Bob']);
```

**Fixture**

Each fixture must have a property `$table` with the table name and an array `$records` with the
default data to insert as well as getters for both properties.

```php
<?php

namespace App\Test\Fixture;

use TestTraits\Interface\FixtureInterface;

class UserFixture implements FixtureInterface
{
    // Table name
    public string $table = 'user';

    // Database records
    public array $records = [
        // Only one record is relevant as the data is defined in the test function
        [
            'id' => 1,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ],
    ];
    
    public function getTable(): string
    {
        return $this->table;
    }

    public function getRecords(): array
    {
        return $this->records;
    }
}
```

**Insert fixture with custom attributes**

To define custom data that should override the default values of the fixture class,
the `insertFixture()` function can be used.  
The first parameter is the fixture object,
the second is optional and accepts arrays of attributes.

An array of attributes contains the data for one database row
e.g.:   
`['field_name' => 'value', 'other_field_name' => 'other_value']`.

Multiple rows can also be inserted with one function call.
Thanks to the argument unpacking operator,
more than one attribute array can be passed to the function as shown below.

Not all fields of the table need to be specified.
The values of the first fixture `$records` entry will be used for the unspecified fields.

The function returns an array with the inserted data including the auto-incremented id
or an array for each row that was inserted.

```php
<?php

namespace App\Test\TestCase;

use PHPUnit\Framework\TestCase;
use TestTraits\Trait\FixtureTestTrait;

final class GetUsersTestAction extends TestCase
{
    // ...
    use FixtureTestTrait;

    public function testAction(): void
    {
        // Insert the fixture with the default values
        $defaultUserRow = $this->insertFixture(new UserFixture());
        // $defaultUserRow equals ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe']
        
        // Insert the fixture with the given attributes
        $bobUserRow = $this->insertFixture(new UserFixture(), ['first_name' => 'Bob', ]);
        // $bobUserRow equals ['id' => 2, 'first_name' => 'Bob', 'last_name' => 'Doe']
        
        // Insert 2 rows with the given attributes 
        $jackAndAliceRows = $this->insertFixture(
            new UserFixture(), 
            ['first_name' => 'Jack', 'last_name' => 'Brown'], 
            ['first_name' => 'Alice']
        );
        // $jackAndAliceRows contains the two inserted rows:
        // [
        //      ['id' => 3, 'first_name' => 'Jack', 'last_name' => 'Brown'],
        //      ['id' => 4, 'first_name' => 'Alice', 'last_name' => 'Doe']
        // ]
        
        // Multiple rows can also be inserted when passing one attribute argument that contains
        // multiple arrays for each row
        $benAndEveRows = $this->insertFixture(
            new UserFixture(), [
                ['first_name' => 'Ben', 'last_name' => 'Smith'], 
                ['first_name' => 'Eve', 'last_name' => 'Taylor']
            ]
        );
        
        // ...
    }
}
```

The `FixtureTestTrait` uses the `DatabaseTestTrait` for the interaction with the database.

### HttpTestTrait

Requirements

* Any PSR-7 and PSR-17 factory implementation.

```
composer require nyholm/psr7-server
composer require nyholm/psr7
```

**Provided methods**

* `createRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface`
* `createFormRequest(string $method, $uri, array $data = null): ServerRequestInterface`
* `createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface`

**Usage**

```php
<?php

namespace App\Test\TestCase;

use PHPUnit\Framework\TestCase;
use TestTraits\Trait\ContainerTestTrait;
use TestTraits\Trait\HttpTestTrait;

class GetUsersTestAction extends TestCase
{
    use ContainerTestTrait;
    use HttpTestTrait;
     
    public function test(): void
    {
        $request = $this->createRequest('GET', '/api/users');
        $response = $this->app->handle($request);
        $this->assertSame(200, $response->getStatusCode());
    }
}
```

Creating a request with query string parameters:

```php
$request = $this->createRequest('GET', '/api/users')
    ->withQueryParams($queryParams);
```

## RouteTestTrait

A Slim 4 framework router test trait.

Requirements

* A Slim 4 framework application

**Provided method**

```php
urlFor(string $routeName, array $data = [], array $queryParams = []): string
```

**Usage**

```php
<?php

namespace App\Test\TestCase;

use PHPUnit\Framework\TestCase;
use TestTraits\Trait\ContainerTestTrait;
use TestTraits\Trait\HttpTestTrait;
use TestTraits\Trait\RouteTestTrait;

final class GetUsersTestAction extends TestCase
{
    use ContainerTestTrait;
    use HttpTestTrait;
    use RouteTestTrait;
     
    public function test(): void
    {
        $request = $this->createRequest('GET', $this->urlFor('get-users'));
        $response = $this->app->handle($request);
        $this->assertSame(200, $response->getStatusCode());
    }
}
```

Creating a request with a named route and query string parameters:

```php
$request = $this->createRequest('GET',  $this->urlFor('get-users'))
    ->withQueryParams($queryParams);
```

### MailerTestTrait

Requirements: `composer require symfony/mailer`

DI container setup example:

```php
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\EventListener\EnvelopeListener;
use Symfony\Component\Mailer\EventListener\MessageListener;
use Symfony\Component\Mailer\EventListener\MessageLoggerListener;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;
// ...

return [
    // Mailer
    MailerInterface::class => function (ContainerInterface $container) {
        return new Mailer($container->get(TransportInterface::class));
    },

    // Mailer transport
    TransportInterface::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['smtp'];

        // smtp://user:pass@smtp.example.com:25
        $dsn = sprintf(
            '%s://%s:%s@%s:%s',
            $settings['type'],
            $settings['username'],
            $settings['password'],
            $settings['host'],
            $settings['port']
        );

        $eventDispatcher = $container->get(EventDispatcherInterface::class);

        return Transport::fromDsn($dsn, $eventDispatcher);
    },

    EventDispatcherInterface::class => function () {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(new MessageListener());
        $eventDispatcher->addSubscriber(new EnvelopeListener());
        $eventDispatcher->addSubscriber(new MessageLoggerListener());

        return $eventDispatcher;
    },
    
    // ...
],
```

**Usage**

PHPUnit test case:

```php
<?php

namespace App\Test\TestCase;

use PHPUnit\Framework\TestCase;
use TestTraits\Trait\ContainerTestTrait;
use TestTraits\Trait\MailerTestTrait;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class MailerExampleTest extends TestCase
{
    use ContainerTestTrait;
    use MailerTestTrait;

    public function testMailer(): void
    {
        $mailer = $this->container->get(MailerInterface::class);

        // Send email
        $email = (new Email())
            ->from('hello@example.com')
            ->to('you@example.com')
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>My HTML content</p>');

        $mailer->send($email);

        $this->assertEmailCount(1);
        $this->assertEmailTextBodyContains($this->getMailerMessage(), 'Sending emails is fun again!');
        $this->assertEmailHtmlBodyContains($this->getMailerMessage(), '<p>My HTML content</p>');
    }
}
```

## License

This project is licensed under the MIT Licence — see the
[LICENCE](https://github.com/samuelgfeller/test-traits/blob/master/LICENSE) file for details.
