# PHP Redis PUB/SUB Adapter

Redis PUB/SUB adapter implemented in PHP8 that allows to fire events across different domains.

## Features

- Subscribe to multiple channels at once
- Publish multiple messages at one
- Handle every message by its own class handler
- Prevent handling messages wrongly published on subscribed channels

## Built With

* [PHP](https://www.php.net) - The popular general-purpose scripting language that is especially suited to web development.
* [Redis](https://redis.io/) - The open source (BSD licensed), in-memory data structure store, used as a database, cache, and message broker.
* [Monolog](https://github.com/Seldaek/monolog) - Library to send your logs to files, sockets, inboxes, databases and various web services.

## Getting Started

### Prerequisites

This library requires PHP 8.0 or higher.

### Installing

```bash
composer require alcidesrc/php-redis-pubsub
```

### Usage

#### Creating custom handlers

First you must extends the provided `AbstractHandler` and customise the logic as follow:

```php
<?php

namespace App\Handlers;

use PhpRedisPubSub\Handlers\AbstractHandler;
use PhpRedisPubSub\Interfaces\Message as MessageInterface;
use Psr\Log\LoggerInterface;

class YourClassHandler extends AbstractHandler
{
    //-----------------------------------------------------------------------------------------------------------------
    // PUBLIC METHODS
    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @return string
     */
    public static function eventName(): string
    {
        return 'custom-event-name';
    }

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @return bool     Returns true in case of success; false i.o.c.
     */
    public function __invoke(): bool
    {
        // Your business logic goes here...

        $this->logger->info(
            message: 'Event [ '. self::eventName() .' ] has been fired!',
            context: [
                'id' => $this->message->id,
            ]
        );

        return true;
    }

    //-----------------------------------------------------------------------------------------------------------------
    //-----------------------------------------------------------------------------------------------------------------
}

```

#### Subscribe to channels

```php
<?php

require_once __DIR__ .'/../vendor/autoload.php';

use App\Handlers\DemoEventHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PhpRedisPubSub\Interfaces\Message as MessageInterface;
use PhpRedisPubSub\PhpRedisPubSub;
use Predis\Client;

$REGISTERED_EVENTS = [
    DemoEventHandler::eventName() => DemoEventHandler::class,
];

$client = new Client(
    parameters: [
        'scheme'             => 'tcp',
        'host'               => 'redis',
        'port'               => 6379,
        'database'           => 0,
        'read_write_timeout' => 0,
    ],
    options: [
        'cluster' => 'redis',
        'prefix'  => 'redis_pubsub_database_',
    ],
);

$logger = (new Logger(
    name: 'php-redis-pubsub',
))->pushHandler(
    handler: new StreamHandler(
        stream: 'php://stdout',
        level: Logger::INFO,
    )
);

(new PhpRedisPubSub(client: $client, logger: $logger))
    ->withHandler(function (MessageInterface $message) use ($logger, $REGISTERED_EVENTS) {
        // Prevent dealing with messages wrongly posted on this channel
        if (! array_key_exists($message->getName(), $REGISTERED_EVENTS)) {
            return;
        }

        $className = $REGISTERED_EVENTS[$message->getName()];

        // Invokable class
        (new $className(logger: $logger, message: $message))();
    })
    ->subscribe('channel:name1', 'channel:name2');
```

#### Publish messages to channels

```php
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PhpRedisPubSub\PhpRedisPubSub;
use Predis\Client;

$client = new Client(
    parameters: [
        'scheme'             => 'tcp',
        'host'               => 'redis',
        'port'               => 6379,
        'database'           => 0,
        'read_write_timeout' => 0,
    ],
    options: [
        'cluster' => 'redis',
        'prefix'  => 'redis_pubsub_database_',
    ],
);

$logger = (new Logger(
    name: 'php-redis-pubsub',
))->pushHandler(
    handler: new StreamHandler(
        stream: 'php://stdout',
        level: Logger::INFO,
    )
);

(new PhpRedisPubSub(client: $client, logger: $logger))
    ->addMessage('custom-event-name', ['id' => 12345])
    ->publish('channel:name1', 'channel:name2');
```

### Running the tests

```bash
./vendor/bin/phpunit
```

## Authors

* **Alcides Ramos** - *Initial work* - [GitHub](https://github.com/alcidesrc)

## License

This project is licensed under the MIT License - see the [LICENSE](https://raw.githubusercontent.com/AlcidesRC/php-redis-pubsub/main/LICENSE) file for details

