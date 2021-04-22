<?php

namespace PhpRedisPubSub\Handlers;

use PhpRedisPubSub\Interfaces\Message as MessageInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractHandler
{
    //-----------------------------------------------------------------------------------------------------------------
    // PUBLIC METHODS
    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @param  LoggerInterface $logger
     * @param  MessageInterface $message
     *
     * @return void
     */
    public function __construct(
        protected LoggerInterface $logger,
        protected MessageInterface $message
    ) {
    }

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @return void
     */
    public function __destruct()
    {
        unset($this->logger);
        unset($this->message);
    }

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @return bool
     */
    abstract public function __invoke(): bool;

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @return string
     */
    abstract public static function eventName(): string;

    //-----------------------------------------------------------------------------------------------------------------
    //-----------------------------------------------------------------------------------------------------------------
}
