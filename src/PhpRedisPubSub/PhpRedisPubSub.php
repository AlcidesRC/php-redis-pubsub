<?php

namespace PhpRedisPubSub;

use PhpRedisPubSub\Interfaces\Adapter as AdapterInterface;
use PhpRedisPubSub\Interfaces\Message as MessageInterface;
use PhpRedisPubSub\Models\Message;
use Predis\Client;
use Psr\Log\LoggerInterface;

class PhpRedisPubSub implements AdapterInterface
{
    //-----------------------------------------------------------------------------------------------------------------
    // PROPERTIES
    //-----------------------------------------------------------------------------------------------------------------

    public const TYPE_MESSAGE = 'message';

    /**
     * @var Client
     */
    protected mixed $client;

    /**
     * @var array<MessageInterface>
     */
    protected array $messages;

    /**
     * @var callable|null
     */
    protected mixed $handler;

    /**
     * @var LoggerInterface
     */
    protected mixed $logger;

    //-----------------------------------------------------------------------------------------------------------------
    // PUBLIC METHODS
    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @param Client $client
     * @param LoggerInterface $logger
     */
    public function __construct(Client $client, LoggerInterface $logger)
    {
        $this->client        = $client;
        $this->handler       = null;
        $this->logger        = $logger;
        $this->messages      = [];
    }

    //-----------------------------------------------------------------------------------------------------------------

    public function __destruct()
    {
        unset($this->client);
        unset($this->handler);
        unset($this->logger);
        unset($this->messages);
    }

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @return callable
     */
    public function getHandler(): callable
    {
        return $this->handler;
    }

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @return array<string,mixed>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    //-----------------------------------------------------------------------------------------------------------------
    // PUB(LISH)
    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @param string $name
     * @param array<string,mixed> $properties
     *
     * @return self
     */
    public function addMessage(string $name, array $properties = []): self
    {
        $this->messages[] = new Message(
            name: $name,
            properties: $properties
        );
        return $this;
    }

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @param MessageInterface ...$messages
     *
     * @return self
     */
    public function withMessages(MessageInterface ...$messages): self
    {
        $this->messages = $messages;
        return $this;
    }

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * Publish message(s) to channel(s).
     *
     * @param string ...$channels
     *
     * @return void
     */
    public function publish(string ...$channels): void
    {
        if (! count($channels)) {
            $this->logger->warning(
                message: 'No any channel has been specified'
            );
            return;
        }

        if (! count($this->messages)) {
            $this->logger->warning(
                message: 'No any message has been specified',
                context: ['channels' => $channels]
            );
            return;
        }

        array_walk(
            $channels,
            function (string $channel) {
                array_walk(
                    $this->messages,
                    function (MessageInterface $message) use ($channel) {
                        $payload = serialize($message);

                        $this->client->publish($channel, $payload);

                        $this->logger->info(
                            message: 'Successfully published to channel',
                            context: ['channel' => $channel, 'payload' => $payload]
                        );
                    }
                );
            }
        );

        // Clean up internal messages's container to avoid republish same content again
        $this->messages = [];
    }

    //-----------------------------------------------------------------------------------------------------------------
    // SUB(SCRIBE)
    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @param callable $handler
     *
     * @return self
     */
    public function withHandler(callable $handler): self
    {
        $this->handler = $handler;
        return $this;
    }

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * Subscribe a handler to a channel.
     *
     * @param string ...$channels
     *
     * @return void
     */
    public function subscribe(string ...$channels): void
    {
        $loop = $this->client->pubSubLoop();

        array_walk(
            $channels,
            function ($channel) use ($loop) {
                $loop->subscribe(
                    channel: $channel
                );

                $this->logger->info(
                    message: 'Successfully subscribed to channel',
                    context: ['channel' => $channel]
                );
            }
        );

        while ($contents = $loop->current()) {
            if ($contents->kind !== self::TYPE_MESSAGE) {
                continue;
            }

            call_user_func($this->handler, unserialize($contents->payload));

            $this->logger->info(
                message: 'Message successfully received from channel',
                context: ['channel' => $contents->channel, 'payload' => $contents->payload]
            );

            $loop->next();
        }

        unset($loop);
    }

    //-----------------------------------------------------------------------------------------------------------------
    // UNSUBSCRIBE
    //-----------------------------------------------------------------------------------------------------------------

    /**
     * Unsubscribes from the specified channels.
     *
     * @param string ...$channels
     *
     * @return void
     */
    public function unsubscribe(string ...$channels): void
    {
        $loop = $this->client->pubSubLoop();

        array_walk(
            $channels,
            function ($channel) use ($loop) {
                $loop->unsubscribe($channel);

                $this->logger->info(
                    message: 'Successfully unsubscribed from channel',
                    context: ['channel' => $channel]
                );
            }
        );

        unset($loop);
    }

    //-----------------------------------------------------------------------------------------------------------------
    //-----------------------------------------------------------------------------------------------------------------
}
