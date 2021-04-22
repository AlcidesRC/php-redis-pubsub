<?php

namespace PhpRedisPubSub\Tests;

use DateTimeImmutable;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PhpRedisPubSub\Interfaces\Message as MessageInterface;
use PhpRedisPubSub\Models\Message;
use PhpRedisPubSub\PhpRedisPubSub;
use PHPUnit\Framework\TestCase;
use Predis\Client;
use Predis\PubSub\Consumer;
use Psr\Log\LoggerInterface;

final class SubscriberTest extends TestCase
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

    protected const REGEXP_UUID = '/[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89aAbB][a-f0-9]{3}-[a-f0-9]{12}/';

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
     */
    public function testHandlerIsProperlyDefined()
    {
        $adapter = new PhpRedisPubSub(
            client: $this->client,
            logger: $this->logger
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

        $adapter->withHandler(function (MessageInterface $message) {
            return $message->getName();
        });

        // Check handler
        $this->assertNotNull($adapter->getHandler());
        $this->assertSame('Closure', get_class($adapter->getHandler()));
    }

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @covers PhpRedisPubSub\PhpRedisPubSub
     *
     * @testWith [{"name":"demo:event","properties":{"id":123}}, {"name":"demo:channel"}]
     */
    public function testProperlySubscribedToChannel(array $data, array $channel)
    {
        $message = new Message($data['name'], $data['properties']);

        //- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $mockedPubSubLoop = $this->createStub(Consumer::class);

        $mockedPubSubLoop
            ->expects($this->once())
            ->method('subscribe')
            ->with($channel['name']);

        $mockedPubSubLoop
            ->method('current')
            ->will(
                $this->onConsecutiveCalls(
                    (object) [
                        'kind'    => PhpRedisPubSub::TYPE_MESSAGE,
                        'channel' => $channel['name'],
                        'payload' => serialize($message),
                    ],
                    null,
                )
            );

        $mockedPubSubLoop
            ->method('next')
            ->willReturn(null);

        $mockedClient = $this->createStub(Client::class);
        $mockedClient
            ->method('pubSubLoop')
            ->willReturn($mockedPubSubLoop);

        $mockedLogger = $this->createStub(Logger::class);
        $mockedLogger->method('info')->with($this->anything());
        $mockedLogger->method('warning')->with($this->anything());

        //- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $adapter = new PhpRedisPubSub(
            client: $mockedClient,
            logger: $mockedLogger,
        );

        $adapter->withHandler(function (MessageInterface $message) use ($data) {
            $date = new DateTimeImmutable();
            $date->setTimestamp($message->getTimestamp());

            // Check class
            $this->assertInstanceOf(Message::class, $message);

            // Check message name
            $this->assertIsString($message->getName());
            $this->assertNotNull($message->getName());
            $this->assertNotEmpty($message->getName());
            $this->assertSame($data['name'], $message->getName());

            // Check message UUID
            $this->assertNotNull($message->getUuid());
            $this->assertMatchesRegularExpression(self::REGEXP_UUID, $message->getUuid());

            // Check message Timestamp
            $this->assertNotNull($message->getTimestamp());
            $this->assertIsInt($message->getTimestamp());
            $this->assertSame(date('Y-m-d'), $date->format('Y-m-d'));
        });

        $adapter->subscribe($channel['name']);
    }

    //-----------------------------------------------------------------------------------------------------------------
    //-----------------------------------------------------------------------------------------------------------------
}

