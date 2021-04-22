<?php

namespace PhpRedisPubSub\Interfaces;

use PhpRedisPubSub\Interfaces\Message as MessageInterface;
use Predis\Client;
use Psr\Log\LoggerInterface;

interface Adapter
{
    //-----------------------------------------------------------------------------------------------------------------
    // PUBLIC METHODS
    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @return Client
     */
    public function getClient(): Client;

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @return callable
     */
    public function getHandler(): callable;

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @return array<string,mixed>
     */
    public function getMessages(): array;

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface;

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @param string $name
     * @param array<string,mixed> $properties
     *
     * @return self
     */
    public function addMessage(string $name, array $properties = []): self;

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @param MessageInterface ...$messages
     *
     * @return self
     */
    public function withMessages(MessageInterface ...$messages): self;

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * Publish message(s) to channel(s).
     *
     * @param string ...$channels
     *
     * @return void
     */
    public function publish(string ...$channels): void;

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @param callable $handler
     *
     * @return self
     */
    public function withHandler(callable $handler): self;

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * Subscribe a handler to a channel.
     *
     * @param string ...$channels
     *
     * @return void
     */
    public function subscribe(string ...$channels): void;

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * Unsubscribes from the specified channels.
     *
     * @param string ...$channels
     *
     * @return void
     */
    public function unsubscribe(string ...$channels): void;

    //-----------------------------------------------------------------------------------------------------------------
    //-----------------------------------------------------------------------------------------------------------------
}
