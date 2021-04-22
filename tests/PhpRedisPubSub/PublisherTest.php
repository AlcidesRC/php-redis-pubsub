<?php

namespace PhpRedisPubSub\Tests;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PhpRedisPubSub\Models\Message;
use PhpRedisPubSub\PhpRedisPubSub;
use PHPUnit\Framework\TestCase;
use Predis\Client;
use Psr\Log\LoggerInterface;

final class PublisherTest extends TestCase
{
    //-----------------------------------------------------------------------------------------------------------------
    // PROPERTIES
    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @var Client
     */
    protected Client $client;

    /**
     * @var Logger
     */
    protected Logger $logger;

    //-----------------------------------------------------------------------------------------------------------------
    // FIXTURES
    //-----------------------------------------------------------------------------------------------------------------

    protected function setUp(): void
    {
        $this->client = new Client(
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

        $this->logger = (new Logger(
            name: 'php-redis-pubsub'
        ))->pushHandler(
            handler: new StreamHandler(
                stream: 'php://stdout',
                level: Logger::INFO,
            )
        );
    }

    //-----------------------------------------------------------------------------------------------------------------
    // PUBLIC METHODS
    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @covers PhpRedisPubSub\PhpRedisPubSub
     *
     * @testWith [{"name":"demo:event","properties":{"id":123}}, {"name":"demo:channel"}]
     */
    public function testMessageIsProperlyPublished(array $data, array $channel)
    {
        $message = new Message($data['name'], $data['properties']);

        //- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $mockedClient = $this->createStub(Client::class);
        $mockedClient
            ->expects($this->once())
            ->method('__call')
            ->with(
                commandID: 'publish',
                arguments: [$channel['name'], serialize($message)]
            )
            ->willReturn(null);

        $mockedLogger = $this->createStub(Logger::class);
        $mockedLogger->method('info')->with($this->anything());
        $mockedLogger->method('warning')->with($this->anything());

        //- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $adapter = new PhpRedisPubSub(
            client: $mockedClient,
            logger: $mockedLogger,
        );

        // Check class
        $this->assertInstanceOf(PhpRedisPubSub::class, $adapter);

        // Check messages
        $this->assertCount(0, $adapter->getMessages());

        // Check client
        $this->assertNotNull($adapter->getClient());
        $this->assertInstanceOf(Client::class, $adapter->getClient());

        // Check logger
        $this->assertNotNull($adapter->getLogger());
        $this->assertInstanceOf(LoggerInterface::class, $adapter->getLogger());

        $adapter->withMessages($message);

        // Check messages
        $this->assertCount(1, $adapter->getMessages());
        $this->assertEquals($message, $adapter->getMessages()[0]);

        $adapter->publish($channel['name']);

        // Check messages
        $this->assertCount(0, $adapter->getMessages());
    }

    //-----------------------------------------------------------------------------------------------------------------
    //-----------------------------------------------------------------------------------------------------------------
}
