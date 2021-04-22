<?php

namespace PhpRedisPubSub\Models;

use PhpRedisPubSub\Interfaces\Message as MessageInterface;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

class Message implements MessageInterface
{
    //-----------------------------------------------------------------------------------------------------------------
    // PROPERTIES
    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @var array<string,mixed>
     */
    private array $properties;

    private string $name;

    private string $uuid;

    private int $timestamp;

    //-----------------------------------------------------------------------------------------------------------------
    // PUBLIC METHODS
    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @param string $name
     * @param array<string,mixed>|null $properties
     */
    public function __construct(string $name, ?array $properties = null)
    {
        $this->name       = $name;
        $this->properties = $properties ?? [];
        $this->timestamp  = (int) (new DateTimeImmutable('now'))->getTimestamp();
        $this->uuid       = (Uuid::uuid4())->toString();
    }

    //-----------------------------------------------------------------------------------------------------------------

    public function __destruct()
    {
        unset($this->name);
        unset($this->properties);
        unset($this->timestamp);
        unset($this->uuid);
    }

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @return mixed
     */
    public function getTimestamp(?string $format = null): mixed
    {
        if (is_null($format)) {
            return (int) $this->timestamp;
        }

        return (new \DateTimeImmutable())
            ->setTimestamp($this->timestamp)
            ->format($format);
    }

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function __set(string $key, mixed $value): void
    {
        $this->properties[$key] = $value;
    }

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function __get(string $key): mixed
    {
        return $this->properties[$key] ?? null;
    }

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @param string $key
     *
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return isset($this->properties[$key]);
    }

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @return string
     */
    public function __toString(): string
    {
        try {
            return json_encode(
                [
                    'name'       => $this->name,
                    'uuid'       => $this->uuid,
                    'properties' => $this->properties,
                    'timestamp'  => $this->timestamp,
                ],
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            return '';
        }
    }

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @return array<string,mixed>
     */
    public function __debugInfo(): array
    {
        return $this->toArray();
    }

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return json_decode((string) $this, true);
    }

    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @return object
     */
    public function toObject(): object
    {
        return json_decode((string) $this, false);
    }

    //-----------------------------------------------------------------------------------------------------------------
    //-----------------------------------------------------------------------------------------------------------------
}
