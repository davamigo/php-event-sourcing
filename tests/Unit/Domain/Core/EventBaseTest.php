<?php

namespace Test\Unit\Domain\Core;

use Davamigo\Domain\Core\Entity\EntityBase;
use Davamigo\Domain\Core\Event\EventBase;
use Davamigo\Domain\Core\Event\EventException;
use Davamigo\Domain\Core\Serializable\Serializable;
use Davamigo\Domain\Core\Serializable\SerializableTrait;
use Davamigo\Domain\Core\Uuid\Uuid;
use PHPUnit\Framework\TestCase;

/**
 * Test of class Davamigo\Domain\Core\Event\EventBase
 *
 * @package Test\Unit\Domain\Core
 * @author davamigo@gmail.com
 *
 * @group Test_Unit_Domain_Core_EventBase
 * @group Test_Unit_Domain_Core
 * @group Test_Unit_Domain
 * @group Test_Unit
 * @group Test
 * @test
 */
class EventBaseTest extends TestCase
{
    /**
     * Test minimal constructor of EventBase class
     */
    public function testMinimalConstructor()
    {
        $entity = new class extends EntityBase {
            use SerializableTrait;
        };

        $event = $this->createEvent('event_name', 'insert', $entity);

        $this->assertEquals('event', $event->type());
        $this->assertEquals('insert', $event->action());
        $this->assertEquals('event_name', $event->name());
        $this->assertEquals($entity, $event->payload());
        $this->assertInstanceOf(Uuid::class, $event->uuid());
        $this->assertInstanceOf(\DateTime::class, $event->createdAt());
        $this->assertEquals([], $event->metadata());
        $this->assertNull($event->topic());
        $this->assertNull($event->routingKey());
    }

    /**
     * Test full constructor of EventBase class
     */
    public function testFullConstructor()
    {
        $serializable = new class implements Serializable {
            use SerializableTrait;
        };

        $metadata = [
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'topic' => 'the_topic',
            'routing_key' => 'the_routing_key'
        ];

        $event = $this->createEvent(
            'the_name',
            'update',
            $serializable,
            'baf44167-95f1-44d3-b9fd-645b5f05dd9d',
            \DateTime::createFromFormat('d-m-Y', '20-02-2002'),
            [ 'a' => 1, 'b' => 2, 'c' => 3 ]
        );
        $event->setTopic('the_topic');
        $event->setRoutingKey('the_routing_key');

        $this->assertEquals('event', $event->type());
        $this->assertEquals('update', $event->action());
        $this->assertEquals('the_name', $event->name());
        $this->assertEquals($serializable, $event->payload());
        $this->assertEquals('baf44167-95f1-44d3-b9fd-645b5f05dd9d', $event->uuid()->toString());
        $this->assertEquals('20-02-2002', $event->createdAt()->format('d-m-Y'));
        $this->assertEquals($metadata, $event->metadata());
    }

    /**
     * Test constructor of EventBase class throws an exception when no name
     */
    public function testConstructorWithoutTypeThrowsAnException()
    {
        $this->expectException(EventException::class);

        $serializable = new class implements Serializable {
            use SerializableTrait;
        };

        $this->createEvent('', 'insert', $serializable);
    }

    /**
     * Returns a new EventBase object
     *
     * @param $name
     * @param $action
     * @param $payload
     * @param $uuid
     * @param $createdAt
     * @param $metadata
     * @return EventBase
     */
    private function createEvent(
        $name = null,
        $action = null,
        $payload = null,
        $uuid = null,
        $createdAt = null,
        $metadata = []
    ) {
        return new class($name, $action, $payload, $metadata, $createdAt, $uuid) extends EventBase {
            use SerializableTrait;
        };
    }
}
