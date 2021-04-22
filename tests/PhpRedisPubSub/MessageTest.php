<?php

namespace PhpRedisPubSub\Tests;

use PhpRedisPubSub\Models\Message;
use PHPUnit\Framework\TestCase;

final class MessageTest extends TestCase
{
    //-----------------------------------------------------------------------------------------------------------------
    // PUBLIC METHODS
    //-----------------------------------------------------------------------------------------------------------------

    /**
     * @covers Models\Message
     *
     * @testWith [{"name":"demo:event:1","properties":{"id":123}}]
     */
    public function testMessageModelIsProperlyDefined(array $data)
    {
        $message = new Message($data['name'], $data['properties']);

        // Check class
        $this->assertInstanceOf(Message::class, $message);

        // Check name
        $this->assertIsString($message->getName());
        $this->assertNotEmpty($message->getName());
        $this->assertSame($data['name'], $message->getName());

        // Check UUID
        $this->assertIsString($message->getUUID());
        $this->assertNotEmpty($message->getUUID());

        // Check Timestamp
        $this->assertIsInt($message->getTimestamp());
        $this->assertIsString($message->getTimestamp('Y-m-d H:i:s'));

        // Check property Id
        $this->assertIsInt($message->id);
        $this->assertSame($data['properties']['id'], $message->id);

        // Check conversions
        $this->assertIsString((string) $message);
        $this->assertNotEmpty((string) $message);
        $this->assertStringStartsWith('{"name":"'. $data['name'] .'","uuid":', (string) $message);

        $this->assertIsArray($message->toArray());
        $this->assertArrayHasKey('name', $message->toArray());
        $this->assertArrayHasKey('uuid', $message->toArray());
        $this->assertArrayHasKey('timestamp', $message->toArray());
        $this->assertArrayHasKey('properties', $message->toArray());

        $this->assertIsObject($message->toObject());
        $this->assertObjectHasAttribute('name', $message->toObject());
        $this->assertObjectHasAttribute('uuid', $message->toObject());
        $this->assertObjectHasAttribute('timestamp', $message->toObject());
        $this->assertObjectHasAttribute('properties', $message->toObject());

        // Check properties

        $this->assertSame($message->id, $message->toArray()['properties']['id']);
        $this->assertSame($message->id, $message->toObject()->properties->id);
    }

    //-----------------------------------------------------------------------------------------------------------------
    //-----------------------------------------------------------------------------------------------------------------
}
