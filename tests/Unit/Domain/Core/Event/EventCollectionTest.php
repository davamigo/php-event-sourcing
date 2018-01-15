<?php

namespace Test\Unit\Domain\Core\Event;

use Davamigo\Domain\Core\Event\Event;
use Davamigo\Domain\Core\Event\EventBase;
use Davamigo\Domain\Core\Event\EventCollection;
use Davamigo\Domain\Core\Event\EventException;
use Davamigo\Domain\Core\Entity\EntityBase;
use Davamigo\Domain\Core\Serializable\SerializableTrait;
use PHPUnit\Framework\TestCase;

/**
 * Test of class Davamigo\Domain\Core\Event\EventCollection
 *
 * @package Test\Unit\Domain\Core\Event
 * @author davamigo@gmail.com
 *
 * @group Test_Unit_Domain_Core_Event_Collection
 * @group Test_Unit_Domain_Core_Event
 * @group Test_Unit_Domain_Core
 * @group Test_Unit_Domain
 * @group Test_Unit
 * @group Test
 * @test
 */
class EventCollectionTest extends TestCase
{
    /**
     * EventCollection::__construct()
     * EventCollection::toArray()
     */
    public function testConstructorHappyPath()
    {
        $event1 = $this->createEventStub('event1');
        $event2 = $this->createEventStub('event2');

        $events = [
            'event1' => $event1,
            'event2' => $event2
        ];

        $expected = $events;

        $collection = new EventCollection($events);
        $result = $collection->toArray();

        $this->assertEquals($expected, $result);
    }

    /**
     * EventCollection::__construct()
     * EventCollection::toArray()
     */
    public function testConstructorWhenNoEventNameProvided()
    {
        $event1 = $this->createEventStub('event1');
        $event2 = $this->createEventStub('event2');

        $events = [
            $event1,
            $event2
        ];

        $expected = [
            get_class($event1) => $event1,
            get_class($event2) => $event2

        ];

        $collection = new EventCollection($events);
        $result = $collection->toArray();

        $this->assertEquals($expected, $result);
    }

    /**
     * EventCollection::__construct()
     * EventCollection::toArray()
     */
    public function testConstructorMixedEventAndNoEventNames()
    {
        $event1 = $this->createEventStub('event1');
        $event2 = $this->createEventStub('event2');

        $events = [
            'event1' => $event1,
            $event2
        ];

        $expected = [
            'event1' => $event1,
            get_class($event2) => $event2

        ];

        $collection = new EventCollection($events);
        $result = $collection->toArray();

        $this->assertEquals($expected, $result);
    }

    /**
     * EventCollection::__construct()
     * EventCollection::toArray()
     */
    public function testConstructorWhenEmptyList()
    {
        $events = [];

        $expected = $events;

        $collection = new EventCollection($events);
        $result = $collection->toArray();

        $this->assertEquals($expected, $result);
    }

    /**
     * EventCollection::__construct()
     * EventCollection::toArray()
     */
    public function testConstructorWhenNoEvents()
    {
        $events = [
            'event1' => new \DateTime()
        ];

        $this->expectException(EventException::class);

        new EventCollection($events);
    }

    /**
     * EventCollection::__construct()
     * EventCollection::current()
     * EventCollection::next()
     * EventCollection::rewind()
     * EventCollection::key()
     * EventCollection::valid()
     */
    public function testCurrent()
    {
        $event1 = $this->createEventStub('event1');
        $event2 = $this->createEventStub('event2');

        $events = [
            'event1' => $event1,
            'event2' => $event2
        ];

        $collection = new EventCollection($events);

        $this->assertEquals($event1, $collection->current());
        $this->assertEquals('event1', $collection->key());
        $this->assertTrue($collection->valid());

        $this->assertEquals($event2, $collection->next());
        $this->assertEquals('event2', $collection->key());
        $this->assertTrue($collection->valid());

        $this->assertFalse($collection->next());
        $this->assertNull($collection->key());
        $this->assertFalse($collection->valid());

        $this->assertEquals($event1, $collection->rewind());
        $this->assertEquals('event1', $collection->key());
        $this->assertTrue($collection->valid());
    }

    /**
     * Create event stub to use in the tests
     *
     * @param string $eventName
     * @return Event
     */
    protected function createEventStub(string $eventName) : Event
    {
        return new class ($eventName) extends EventBase {
            use SerializableTrait;
            public function __construct(string $eventName)
            {
                parent::__construct($eventName, 'the_action', new class extends EntityBase {
                    use SerializableTrait;
                });
            }
        };
    }
}
