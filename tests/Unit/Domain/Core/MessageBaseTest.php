<?php

namespace Test\Unit\Domain\Core;

use Davamigo\Domain\Core\Message\Message;
use Davamigo\Domain\Core\Message\MessageBase;
use Davamigo\Domain\Core\Message\MessageException;
use Davamigo\Domain\Core\Serializable\SerializableTrait;
use Davamigo\Domain\Core\Uuid\Uuid;
use PHPUnit\Framework\TestCase;

/**
 * Test of class Davamigo\Domain\Core\Message\MessageBase
 *
 * @package Test\Unit\Domain\Core
 * @author davamigo@gmail.com
 *
 * @group Test_Unit_Domain_Core_MessageBase
 * @group Test_Unit_Domain_Core
 * @group Test_Unit_Domain
 * @group Test_Unit
 * @group Test
 * @test
 */
class MessageBaseTest extends TestCase
{
    /**
     * Test minimal constructor of MessageBase class
     */
    public function testMinimalConstructor()
    {
        $message = $this->createMessage('command', 'message');

        $this->assertEquals('command', $message->type());
        $this->assertEquals('message', $message->name());
        $this->assertInstanceOf(Uuid::class, $message->uuid());
        $this->assertInstanceOf(\DateTime::class, $message->createdAt());
        $this->assertInternalType('array', $message->metadata());
    }

    /**
     * Test regular constructor of MessageBase class
     */
    public function testRegularConstructor()
    {
        $message = $this->createMessage(
            'event',
            'message_name',
            '7776757c-d78f-429c-9c3c-bc3f3e70af70',
            \DateTime::createFromFormat('Y-m-d', '2000-01-01'),
            [ 'some_metadata_key' => 'some_tetadata_value']
        );

        $this->assertEquals('event', $message->type());
        $this->assertEquals('message_name', $message->name());
        $this->assertEquals('7776757c-d78f-429c-9c3c-bc3f3e70af70', $message->uuid()->toString());
        $this->assertEquals('2000-01-01', $message->createdAt()->format('Y-m-d'));
        $this->assertInternalType('array', $message->metadata());
        $this->assertCount(1, $message->metadata());
    }

    /**
     * Test constructor of MessageBase class throws an exception when no type
     */
    public function testConstructorWithoutTypeThrowsAnException()
    {
        $this->expectException(MessageException::class);

        $this->createMessage('', 'name');
    }

    /**
     * Test constructor of MessageBase class throws an exception when no name
     */
    public function testConstructorWithoutNameThrowsAnException()
    {
        $this->expectException(MessageException::class);

        $this->createMessage('type', '');
    }

    /**
     * Test constructor of MessageBase class throws an exception when invalid uuid
     */
    public function testConstructorWithInvalidUuidThrowsAnException()
    {
        $this->expectException(MessageException::class);

        $this->createMessage('type', 'name', 'xx');
    }

    /**
     * Returns a new MessageBase object
     *
     * @param $type
     * @param $name
     * @param $uuid
     * @param $createdAt
     * @param $metadata
     * @return MessageBase
     */
    private function createMessage($type = null, $name = null, $uuid = null, $createdAt = null, $metadata = [])
    {
        return new class($type, $name, $uuid, $createdAt, $metadata) extends MessageBase {
            use SerializableTrait;
        };
    }
}
