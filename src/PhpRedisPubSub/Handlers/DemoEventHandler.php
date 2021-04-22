<?php

namespace PhpRedisPubSub\Handlers;

use PhpRedisPubSub\Interfaces\Message as MessageInterface;
use Psr\Log\LoggerInterface;

class DemoEventHandler extends AbstractHandler
{
    //-----------------------------------------------------------------------------------------------------------------
    // PUBLIC METHODS
    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @return string
     */
    public static function eventName(): string
    {
        return 'demo:event';
    }

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @return bool
     */
    public function __invoke(): bool
    {
        $this->logger->info(
            message: 'DemoEventHandler has been fired.',
            context: [
                'id' => $this->message->id,
            ]
        );

        return true;
    }

    //-----------------------------------------------------------------------------------------------------------------
    //-----------------------------------------------------------------------------------------------------------------
}
