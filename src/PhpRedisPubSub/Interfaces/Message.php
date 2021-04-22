<?php

namespace PhpRedisPubSub\Interfaces;

/**
 * @property int $id
 */
interface Message
{
    /**
     * Get message name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get message timestamp
     *
     * @param string|null $format
     *
     * @return mixed
     */
    public function getTimestamp(?string $format = null): mixed;

    /**
     * Get message UUID
     *
     * @return string
     */
    public function getUuid(): string;

    /**
     * Get message as array
     *
     * @return array<string,mixed>
     */
    public function toArray(): array;

    /**
     * Get message as object
     *
     * @return object
     */
    public function toObject(): object;
}
