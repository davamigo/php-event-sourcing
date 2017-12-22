<?php

namespace Test\Unit\Infrastructure\Core\Amqp;

use Davamigo\Domain\Core\Event\EventBase;
use Davamigo\Domain\Core\Event\EventConsumerException;
use Davamigo\Domain\Core\Serializable\Serializable;
use Davamigo\Domain\Core\Serializable\SerializableTrait;
use Davamigo\Infrastructure\Core\Event\AmqpEventConsumer;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use Psr\Log\NullLogger;

/**
 * Test of class AmqpEventConsumer
 *
 * @package Test\Unit\Infrastructure\Core\Amqp
 * @author davamigo@gmail.com
 *
 * @group Test_Unit_Infrastructure_Core_EventConsumer_Amqp
 * @group Test_Unit_Infrastructure_Core_EventConsumer
 * @group Test_Unit_Infrastructure_Core
 * @group Test_Unit_Infrastructure
 * @group Test_Unit
 * @group Test
 * @test
 */
class AmqpEventConsumerTest extends AmqpTestCase
{
    /**
     * Test AmqpEventConsumer::__construct()
     */
    public function testConstructorMinumumArguments()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Supported events list
        $evenList = [];

        // The logger
        $logger = new NullLogger();

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, $evenList, $logger, []);

        // Assertions
        $this->assertEquals($connection, $this->getPrivateProperty($eventConsumer, 'connection'));
        $this->assertEquals($logger, $this->getPrivateProperty($eventConsumer, 'logger'));
        $this->assertEquals($evenList, $this->getPrivateProperty($eventConsumer, 'events'));
        $this->assertEquals(3600, $this->getPrivateProperty($eventConsumer, 'waitTimeout'));
        $this->assertEquals(5, $this->getPrivateProperty($eventConsumer, 'restartAttempts'));
        $this->assertEquals(15, $this->getPrivateProperty($eventConsumer, 'restartWaitTime'));
    }

    /**
     * Test AmqpEventConsumer::__construct()
     */
    public function testConstructorWithOptions()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Options
        $options = [
            'wait_timeout'      => 101,
            'restart_attempts'  => 102,
            'restart_wait_time' => 103
        ];

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, [], new NullLogger(), $options);

        // Assertions
        $this->assertEquals(101, $this->getPrivateProperty($eventConsumer, 'waitTimeout'));
        $this->assertEquals(102, $this->getPrivateProperty($eventConsumer, 'restartAttempts'));
        $this->assertEquals(103, $this->getPrivateProperty($eventConsumer, 'restartWaitTime'));
    }

    /**
     * Test AmqpEventConsumer::__construct()
     */
    public function testAddSupportedEvents()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, [], new NullLogger(), []);

        // Add the supported events
        $event = new class extends EventBase {
            use SerializableTrait;
            public function __construct()
            {
                $payload = new class implements Serializable {
                    use SerializableTrait;
                    public $value;
                };
                parent::__construct('the_event', $payload);
            }
        };

        $className = get_class($event);

        $expected = [
            'event1'   => $className,
            'event2'   => $className,
            $className => $className
        ];

        $eventConsumer->addSupportedEvents([
            'event1' => $event,
            'event2' => $className,
            $event,
            $className
        ]);

        // Assertions
        $this->assertEquals($expected, $this->getPrivateProperty($eventConsumer, 'events'));
    }

    /**
     * Test AmqpEventConsumer::__construct()
     */
    public function testAddSupportedEventsWhenError()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, [], new NullLogger(), []);

        // Add the supported events
        $this->expectException(EventConsumerException::class);
        $eventConsumer->addSupportedEvents([ 'this_is_the_event' ]);
    }

    /**
     * Test AmqpEventConsumer::__construct()
     */
    public function testEnableBasicConsume()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        $channelMock = $this->createChannelMock();

        $connectionMock
            ->expects($this->once())
            ->method('channel')
            ->willReturn($channelMock);

        $channelMock
            ->expects($this->once())
            ->method('basic_qos');

        $channelMock
            ->expects($this->once())
            ->method('basic_consume');

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, [], new NullLogger(), []);

        // Call enable basic consume
        $result = $this->callPrivateMethod($eventConsumer, 'enableBasicConsume', [ 'resource' ]);

        // Assertions
        $this->assertTrue($this->getPrivateProperty($eventConsumer, 'running'));
        $this->assertEquals($result, $channelMock);
    }

    /**
     * Test AmqpEventConsumer::__construct()
     */
    public function testEnableBasicConsumeWhenError()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        $channelMock = $this->createChannelMock();

        $connectionMock
            ->expects($this->once())
            ->method('channel')
            ->willReturn($channelMock);

        $channelMock
            ->expects($this->once())
            ->method('basic_qos')
            ->willThrowException(new AMQPRuntimeException());

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, [], new NullLogger(), []);

        // Call enable basic consume
        $this->expectException(EventConsumerException::class);
        $this->callPrivateMethod($eventConsumer, 'enableBasicConsume', [ 'resource' => 'something' ]);
    }
}
