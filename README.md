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

#### Subscribe to channels

```php
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PhpRedisPubSub\Handlers\DemoEventHandler;
use PhpRedisPubSub\Interfaces\Message as MessageInterface;
use PhpRedisPubSub\Models\Message;
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
    name: 'php-redis-pubsub'
))->pushHandler(
    handler: new StreamHandler(
        stream: 'php://stdout',
        level: Logger::INFO,
    )
);

(new PhpRedisPubSub(client: $client, logger: $logger))
    ->withHandler(function (MessageInterface $message) use ($logger) {
        $MAP__EVENT_NAME__HANDLER_CLASS = [
            DemoEventHandler::eventName() => DemoEventHandler::class,
        ];

        // Prevent to handle messages wrongly published on subscribed channels
        if (! array_key_exists($message->getName(), $MAP__EVENT_NAME__HANDLER_CLASS)) {
            return;
        }

        $className = $MAP__EVENT_NAME__HANDLER_CLASS[$message->getName()];

        (new $className(logger: $logger, message: $message))();   // Invokable class
    })
    ->subscribe('demo:channel:1', 'demo:channel:2');
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
    name: 'php-redis-pubsub'
))->pushHandler(
    handler: new StreamHandler(
        stream: 'php://stdout',
        level: Logger::INFO,
    )
);

(new PhpRedisPubSub(client: $mockedClient, logger: $mockedLogger))
    ->addMessage('demo:message', ['id' => 12345])
    ->publish('demo:channel:1', 'demo:channel:2');
```

### Running the tests

```bash
./vendor/bin/phpunit
```

## Authors

* **Alcides Ramos** - *Initial work* - [GitHub](https://github.com/alcidesrc)

## License

This project is licensed under the MIT License.

