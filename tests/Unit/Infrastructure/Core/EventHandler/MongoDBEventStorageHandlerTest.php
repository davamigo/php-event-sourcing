<?php

namespace Test\Unit\Infrastructure\Core\EventHandler;

use Davamigo\Domain\Core\Event\Event;
use Davamigo\Domain\Core\Event\EventBase;
use Davamigo\Domain\Core\EventHandler\EventHandlerException;
use Davamigo\Domain\Core\Serializable\Serializable;
use Davamigo\Domain\Core\Serializable\SerializableTrait;
use Davamigo\Infrastructure\Config\MongoDBConfigurator;
use Davamigo\Infrastructure\Core\EventHandler\MongoDBEventStorageHandler;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;
use MongoDB\Exception\BadMethodCallException;
use PHPUnit\Framework\MockObject\Matcher\Invocation;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use Test\Unit\Infrastructure\Core\AdvancedTestCase;

/**
 * Test of class MongoDBEventStorageHandler
 *
 * @package Test\Unit\Infrastructure\Core\EventHandler
 * @author davamigo@gmail.com
 *
 * @group Test_Unit_Infrastructure_Core_EventHandler_MongoDBStorage
 * @group Test_Unit_Infrastructure_Core_EventHandler
 * @group Test_Unit_Infrastructure_Core
 * @group Test_Unit_Infrastructure
 * @group Test_Unit
 * @group Test
 * @test
 */
class MongoDBEventStorageHandlerTest extends AdvancedTestCase
{
    /**
     * MongoDBEventStorageHandler::__construct()
     */
    public function testConstruct()
    {
        /** @var Client $client */
        $client = $this->createMongoDBClientMock($this->never());
        $config = new MongoDBConfigurator();
        $logger = new NullLogger();

        // Run test
        $handler = new MongoDBEventStorageHandler($client, $config, $logger);

        // Assertions
        $this->assertNotNull($handler);
    }

    /**
     * MongoDBEventStorageHandler::__construct()
     * MongoDBEventStorageHandler::__invoke()
     */
    public function testInvoke()
    {
        /** @var Client $client */
        $client = $this->createMongoDBClientMock($this->once());
        $config = new MongoDBConfigurator();
        $logger = new NullLogger();

        // Test data
        $event = $this->createFakeEvent();

        // Run test
        $handler = new MongoDBEventStorageHandler($client, $config, $logger);
        $handler($event);
    }

    /**
     * MongoDBEventStorageHandler::__construct()
     * MongoDBEventStorageHandler::handleEvent()
     */
    public function testHandleEvent()
    {
        /** @var Client $client */
        $client = $this->createMongoDBClientMock($this->once());
        $config = new MongoDBConfigurator();
        $logger = new NullLogger();

        // Test data
        $event = $this->createFakeEvent();

        // Run test
        $handler = new MongoDBEventStorageHandler($client, $config, $logger);
        $handler->handleEvent($event);
    }

    /**
     * MongoDBEventStorageHandler::__construct()
     * MongoDBEventStorageHandler::handleEvent()
     */
    public function testHandleEventWhenError()
    {
        /** @var Client $client */
        $client = $this->createMongoDBClientMock($this->any());
        $config = new MongoDBConfigurator();
        $logger = new NullLogger();

        /** @var MockObject $clientMock */
        $clientMock = $client;
        $client
            ->expects($this->once())
            ->method('selectDatabase')
            ->willThrowException(new BadMethodCallException());

        // Test data
        $event = $this->createFakeEvent();

        // Run test
        $this->expectException(EventHandlerException::class);
        $handler = new MongoDBEventStorageHandler($client, $config, $logger);
        $handler->handleEvent($event);
    }

    /**
     * MongoDBEventStorageHandler::__construct()
     * MongoDBEventStorageHandler::serializeEvent()
     */
    public function testSerializeEventHappyPath()
    {
        /** @var Client $client */
        $client = $this->createMongoDBClientMock($this->never());
        $config = new MongoDBConfigurator();
        $logger = new NullLogger();

        // Test data
        $event = $this->createFakeEvent(
            '101',
            '102',
            new class implements Serializable {
                use SerializableTrait;
                public $str = 'something';
            },
            [
                'int' => 103
            ]
        );

        $expected = [
            'name'      => '101',
            'action'    => '102',
            'type'      => 'event',
            'payload'   => [
                'str'       => 'something'
            ],
            'metadata'  => [
                'int'       => 103
            ],
            'uuid'      => $event->uuid()->toString(),
            'createdAt' => $event->createdAt()->format(\DateTime::RFC3339)
        ];

        // Run test
        $handler = new MongoDBEventStorageHandler($client, $config, $logger);
        $result = $this->callPrivateMethod($handler, 'serializeEvent', [ $event ]);

        // Assertions
        $this->assertEquals($expected, $result);
    }

    /**
     * MongoDBEventStorageHandler::__construct()
     * MongoDBEventStorageHandler::serializeEvent()
     */
    public function testSerializeEventWhenUnserializableObject()
    {
        /** @var Client $client */
        $client = $this->createMongoDBClientMock($this->never());
        $config = new MongoDBConfigurator();
        $logger = new NullLogger();

        // Test data
        $obj = new \stdClass();
        $event = $this->createFakeEvent(
            '201',
            '202',
            null,
            [
                'obj' => $obj
            ]
        );

        $expected = [
            'name'      => '201',
            'action'    => '202',
            'type'      => 'event',
            'payload'   => [],
            'metadata'  => [
                'obj'       => get_class($obj) . '::class'
            ],
            'uuid'      => $event->uuid()->toString(),
            'createdAt' => $event->createdAt()->format(\DateTime::RFC3339)
        ];

        // Run test
        $handler = new MongoDBEventStorageHandler($client, $config, $logger);
        $result = $this->callPrivateMethod($handler, 'serializeEvent', [ $event ]);

        // Assertions
        $this->assertEquals($expected, $result);
    }

    /**
     * MongoDBEventStorageHandler::__construct()
     * MongoDBEventStorageHandler::serializeEvent()
     */
    public function testSerializeEventWhenCantSerialize()
    {
        /** @var Client $client */
        $client = $this->createMongoDBClientMock($this->never());
        $config = new MongoDBConfigurator();
        $logger = new NullLogger();

        // Test data
        $event = $this->createFakeEvent(
            '201',
            '202',
            new class implements Serializable {
                use SerializableTrait;
                public $obj;
                public function __construct()
                {
                    $this->obj = new \stdClass();
                }

            }
        );

        // Run test
        $this->expectException(EventHandlerException::class);
        $handler = new MongoDBEventStorageHandler($client, $config, $logger);
        $this->callPrivateMethod($handler, 'serializeEvent', [ $event ]);
    }

    /**
     * Creates a MongoDB client mock to use in the tests
     *
     * @return MockObject
     */
    protected function createMongoDBClientMock(Invocation $matcher = null) : MockObject
    {
        $clientMock = $this
            ->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods([ 'selectDatabase' ])
            ->getMock();

        $databaseMock = $this
            ->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->setMethods([ 'selectCollection' ])
            ->getMock();

        $collectionMock = $this
            ->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods([ 'insertOne' ])
            ->getMock();

        if (null === $matcher) {
            $matcher = $this->any();
        }

        $clientMock
            ->expects(clone $matcher)
            ->method('selectDatabase')
            ->willReturn($databaseMock);

        $databaseMock
            ->expects(clone $matcher)
            ->method('selectCollection')
            ->willReturn($collectionMock);

        $collectionMock
            ->expects(clone $matcher)
            ->method('insertOne');

        return $clientMock;
    }

    /**
     * Create fake event use in the tests
     *
     * @param string|null       $eventName
     * @param string|null       $eventAction
     * @param Serializable|null $payload
     * @param array             $metadata
     * @return Event
     */
    protected function createFakeEvent(
        string $eventName = null,
        string $eventAction = null,
        Serializable $payload = null,
        array $metadata = []
    ) : Event {
        $eventName = $eventName ?: 'event1';
        $eventAction = $eventAction ?: 'action1';
        $payload = $payload ?: new class implements Serializable {
            use SerializableTrait;
        };

        return new class ($eventName, $eventAction, $payload, $metadata) extends EventBase {
            use SerializableTrait;
        };
    }
}
