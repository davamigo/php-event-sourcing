<?php

namespace Test\Unit\Infrastructure\Core\EventConsumer;

use Davamigo\Domain\Core\Event\Event;
use Davamigo\Domain\Core\Event\EventBase;
use Davamigo\Domain\Core\EventConsumer\EventConsumerException;
use Davamigo\Domain\Core\Serializable\Serializable;
use Davamigo\Domain\Core\Serializable\SerializableTrait;
use Davamigo\Infrastructure\Core\EventConsumer\AmqpEventConsumer;
use Davamigo\Infrastructure\Config\AmqpConfigurator;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\NullLogger;
use Test\Unit\Infrastructure\Core\AmqpTestCase;

/**
 * Test of class AmqpEventConsumer
 *
 * @package Test\Unit\Infrastructure\Core\EventConsumer
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
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), $evenList, $logger, []);

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
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), $options);

        // Assertions
        $this->assertEquals(101, $this->getPrivateProperty($eventConsumer, 'waitTimeout'));
        $this->assertEquals(102, $this->getPrivateProperty($eventConsumer, 'restartAttempts'));
        $this->assertEquals(103, $this->getPrivateProperty($eventConsumer, 'restartWaitTime'));
    }

    /**
     * Test AmqpEventConsumer::listen()
     */
    public function testListen()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        $channelMock = $this->createChannelMock();

        $connectionMock
            ->expects($this->once())
            ->method('channel')
            ->willReturn($channelMock);

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        /** @var AMQPChannel $channel */
        $channel = $channelMock;
        $channel->callbacks[] = function () {
            // Do nothing
        };

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), []);

        $channelMock
            ->expects($this->once())
            ->method('wait')
            ->willReturnCallback(function () use ($eventConsumer) {
                $eventConsumer->stop();
            });


        // Run the test
        $eventConsumer->listen('resource', function () {
            // Do nothing
        });

        // Assertions
        $this->assertFalse($this->getPrivateProperty($eventConsumer, 'listening'));
    }

    /**
     * Test AmqpEventConsumer::listen()
     */
    public function testListenWhenTimeout()
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
            ->method('wait')
            ->willThrowException(new AMQPTimeoutException());

        $connectionMock
            ->expects($this->once())
            ->method('reconnect')
            ->willThrowException(new AMQPRuntimeException());

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        /** @var AMQPChannel $channel */
        $channel = $channelMock;
        $channel->callbacks[] = function () {
            // Do nothing
        };

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), [
            AmqpEventConsumer::OPTION_RESTART_ATTEMPTS => 0
        ]);

        // Run the test
        $eventConsumer->listen('resource', function () {
            // Do nothing
        });

        // Assertions
        $this->assertFalse($this->getPrivateProperty($eventConsumer, 'listening'));
    }

    /**
     * Test AmqpEventConsumer::listen()
     */
    public function testListenWhenAmqpException()
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
            ->method('wait')
            ->willThrowException(new AMQPRuntimeException());

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        /** @var AMQPChannel $channel */
        $channel = $channelMock;
        $channel->callbacks[] = function () {
            // Do nothing
        };

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), [
            AmqpEventConsumer::OPTION_RESTART_ATTEMPTS => 0
        ]);

        // Run the test
        $eventConsumer->listen('resource', function () {
            // Do nothing
        });

        // Assertions
        $this->assertFalse($this->getPrivateProperty($eventConsumer, 'listening'));
    }

    /**
     * Test AmqpEventConsumer::listen()
     */
    public function testListenWhenOtherException()
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
            ->method('wait')
            ->willThrowException(new \Exception());

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        /** @var AMQPChannel $channel */
        $channel = $channelMock;
        $channel->callbacks[] = function () {
            // Do nothing
        };

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), [
            AmqpEventConsumer::OPTION_RESTART_ATTEMPTS => 0
        ]);

        // Run the test
        $eventConsumer->listen('resource', function () {
            // Do nothing
        });

        // Assertions
        $this->assertFalse($this->getPrivateProperty($eventConsumer, 'listening'));
    }

    /**
     * Test AmqpEventConsumer::stop()
     */
    public function testStop()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), []);

        // Run the test
        $this->setPrivateProperty($eventConsumer, 'listening', true);
        $eventConsumer->stop();

        // Assertions
        $this->assertFalse($this->getPrivateProperty($eventConsumer, 'listening'));
    }

    /**
     * Test AmqpEventConsumer::addSupportedEvents()
     */
    public function testAddSupportedEvents()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), []);

        // Add the supported events
        $event = new class extends EventBase {
            use SerializableTrait;
            public function __construct()
            {
                $payload = new class implements Serializable {
                    use SerializableTrait;
                    public $value;
                };
                parent::__construct('the_event', 'insert', $payload);
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
     * Test AmqpEventConsumer::addSupportedEvents()
     */
    public function testAddSupportedEventsWhenError()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), []);

        // Add the supported events
        $this->expectException(EventConsumerException::class);
        $eventConsumer->addSupportedEvents([ 'this_is_the_event' ]);
    }

    /**
     * Test AmqpEventConsumer::enableBasicConsume()
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
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), []);

        // Run the test
        $result = $this->callPrivateMethod($eventConsumer, 'enableBasicConsume', [ 'resource' ]);

        // Assertions
        $this->assertTrue($this->getPrivateProperty($eventConsumer, 'listening'));
        $this->assertEquals($result, $channelMock);
    }

    /**
     * Test AmqpEventConsumer::enableBasicConsume()
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
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), []);

        // Run the test
        $this->expectException(EventConsumerException::class);
        $this->callPrivateMethod($eventConsumer, 'enableBasicConsume', [ 'resource' => 'something' ]);
    }

    /**
     * Test AmqpEventConsumer::eventReceivedCallback()
     */
    public function testEventReceivedCallback()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        $channelMock = $this->createChannelMock();

        $channelMock
            ->expects($this->once())
            ->method('basic_ack');

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), []);

        // Test data: the event base
        $event = new class extends EventBase {
            use SerializableTrait;
            public function __construct()
            {
                $payload = new class implements Serializable {
                    use SerializableTrait;
                    public $str = '';
                    public $int = 0;
                };
                parent::__construct('my_event', 'insert', $payload);
            }
        };
        $this->callPrivateMethod($eventConsumer, 'addSupportedEvents', [[ 'my_event' => $event ]]);

        // Test data
        $body = '{"name":"my_event","type":"event","payload":{"str":"str","int":10}}';
        $msg = new AMQPMessage($body);
        $msg->delivery_info['channel'] = $channelMock;
        $msg->delivery_info['delivery_tag'] = 'some_tag';

        /** @var EventBase $eventReceived */
        $eventReceived = null;
        $this->setPrivateProperty($eventConsumer, 'callback', function (Event $event) use (&$eventReceived) {
            $eventReceived = $event;
        });

        // Run the test
        $this->callPrivateMethod($eventConsumer, 'eventReceivedCallback', [ $msg ]);
        $payload = $eventReceived->payload();

        // Assertions
        $this->assertInstanceOf(get_class($event), $eventReceived);
        $this->assertEquals('str', $payload->str);
        $this->assertEquals(10, $payload->int);
    }

    /**
     * Test AmqpEventConsumer::eventReceivedCallback()
     */
    public function testEventReceivedCallbackWhenError()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        $channelMock = $this->createChannelMock();

        $channelMock
            ->expects($this->once())
            ->method('basic_reject');

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), []);

        // Test data: the event base
        $event = new class extends EventBase {
            use SerializableTrait;
            public function __construct()
            {
                $payload = new class implements Serializable {
                    use SerializableTrait;
                    public $str = '';
                    public $int = 0;
                };
                parent::__construct('my_event', 'insert', $payload);
            }
        };
        $this->callPrivateMethod($eventConsumer, 'addSupportedEvents', [[ 'my_event' => $event ]]);

        // Test data
        $body = '{"name":"my_event","type":"event","payload":{"str":"str","int":10}}';
        $msg = new AMQPMessage($body);
        $msg->delivery_info['channel'] = $channelMock;
        $msg->delivery_info['delivery_tag'] = 'some_tag';

        $this->setPrivateProperty($eventConsumer, 'callback', function () {
            throw new \Exception();
        });

        // Run the test
        $this->callPrivateMethod($eventConsumer, 'eventReceivedCallback', [ $msg ]);
    }

    /**
     * Test AmqpEventConsumer::decodeEventData()
     */
    public function testDecodeEventData()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), []);

        // Test data: the event base
        $event = new class extends EventBase {
            use SerializableTrait;
            public function __construct()
            {
                $payload = new class implements Serializable {
                    use SerializableTrait;
                    public $str = '';
                    public $int = 0;
                };
                parent::__construct('my_event', 'insert', $payload);
            }
        };
        $this->callPrivateMethod($eventConsumer, 'addSupportedEvents', [[ 'my_event' => $event ]]);

        // Test data
        $jsonData = '{"name":"my_event","type":"event","payload":{"str":"a string","int":45}}';
        $metadata = [ 'meta1' => 'value1', 'meta2' => 'value2' ];

        /** @var Event $event */
        $event = $this->callPrivateMethod($eventConsumer, 'decodeEventData', [ $jsonData, $metadata ]);
        $payload = $event->payload();
        $metadata = $event->metadata();

        // Assertions
        $this->assertInstanceOf(Event::class, $event);
        $this->assertInstanceOf(Serializable::class, $payload);
        $this->assertEquals('my_event', $event->name());
        $this->assertGreaterThanOrEqual(2, count($metadata));
        $this->assertEquals('value1', $metadata['meta1']);
        $this->assertEquals('value2', $metadata['meta2']);
        $this->assertEquals('a string', $payload->str);
        $this->assertEquals(45, $payload->int);
    }

    /**
     * Test AmqpEventConsumer::getEventClass()
     */
    public function testGetEventClassWhenUnrecognizedEvent()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), []);

        // Test data
        $data = [
            'name' => 'my_event',
            'type' => 'event'
        ];

        // Run the test
        $this->expectException(EventConsumerException::class);
        $this->callPrivateMethod($eventConsumer, 'getEventClass', [ $data ]);
    }

    /**
     * Test AmqpEventConsumer::getEventClass()
     */
    public function testGetEventClassWhenEventHasNoName()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), []);

        // Test data
        $data = [
            'type' => 'event'
        ];

        // Run the test
        $this->expectException(EventConsumerException::class);
        $this->callPrivateMethod($eventConsumer, 'getEventClass', [ $data ]);
    }

    /**
     * Test AmqpEventConsumer::getEventClass()
     */
    public function testGetEventClassWhenInvalidEventType()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), []);

        // Test data
        $data = [
            'name' => 'my_event',
            'type' => 'something'
        ];

        // Run the test
        $this->expectException(EventConsumerException::class);
        $this->callPrivateMethod($eventConsumer, 'getEventClass', [ $data ]);
    }

    /**
     * Test AmqpEventConsumer::getEventClass()
     */
    public function testGetEventClassWhenDataHasNoType()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), []);

        // Test data
        $data = [
            'name' => 'my_event'
        ];

        // Run the test
        $this->expectException(EventConsumerException::class);
        $this->callPrivateMethod($eventConsumer, 'getEventClass', [ $data ]);
    }

    /**
     * Test AmqpEventConsumer::sendAck()
     */
    public function testSendAck()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        $channelMock = $this->createChannelMock();

        $channelMock
            ->expects($this->once())
            ->method('basic_ack');

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), []);

        // Test data
        $msg = new AMQPMessage();
        $msg->delivery_info['channel'] = $channelMock;
        $msg->delivery_info['delivery_tag'] = 'some_tag';

        // Run the test
        $this->callPrivateMethod($eventConsumer, 'sendAck', [ $msg ]);
    }

    /**
     * Test AmqpEventConsumer::sendAck()
     */
    public function testSendAckWhenError()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        $channelMock = $this->createChannelMock();

        $channelMock
            ->expects($this->once())
            ->method('basic_ack')
            ->willThrowException(new AMQPRuntimeException());

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), []);

        // Test data
        $msg = new AMQPMessage();
        $msg->delivery_info['channel'] = $channelMock;
        $msg->delivery_info['delivery_tag'] = 'some_tag';

        // Run the test
        $this->expectException(EventConsumerException::class);
        $this->callPrivateMethod($eventConsumer, 'sendAck', [ $msg ]);
    }

    /**
     * Test AmqpEventConsumer::sendNack()
     */
    public function testSendNack()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        $channelMock = $this->createChannelMock();

        $channelMock
            ->expects($this->once())
            ->method('basic_reject');

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), []);

        // Test data
        $msg = new AMQPMessage();
        $msg->delivery_info['channel'] = $channelMock;
        $msg->delivery_info['delivery_tag'] = 'some_tag';

        // Run the test
        $this->callPrivateMethod($eventConsumer, 'sendNack', [ $msg ]);
    }

    /**
     * Test AmqpEventConsumer::sendNack()
     */
    public function testSendNackWhenError()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        $channelMock = $this->createChannelMock();

        $channelMock
            ->expects($this->once())
            ->method('basic_reject')
            ->willThrowException(new AMQPRuntimeException());

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), []);

        // Test data
        $msg = new AMQPMessage();
        $msg->delivery_info['channel'] = $channelMock;
        $msg->delivery_info['delivery_tag'] = 'some_tag';

        // Run the test
        $this->expectException(EventConsumerException::class);
        $this->callPrivateMethod($eventConsumer, 'sendNack', [ $msg ]);
    }

    /**
     * Test AmqpEventConsumer::computeAttempts()
     */
    public function testComputeAttemptsWhenNoRestarAttempsNorTimeoutOccurred()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), [
            AmqpEventConsumer::OPTION_RESTART_ATTEMPTS => 0
        ]);

        // Run the test
        $result = $this->callPrivateMethod($eventConsumer, 'computeAttempts', [ false ]);

        // Assertions
        $this->assertEquals(0, $result);
    }

    /**
     * Test AmqpEventConsumer::computeAttempts()
     */
    public function testComputeAttemptsWhenNoRestarAttempsAndTimeoutOccurred()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), [
            AmqpEventConsumer::OPTION_RESTART_ATTEMPTS => 0
        ]);

        // Run the test
        $result = $this->callPrivateMethod($eventConsumer, 'computeAttempts', [ true ]);

        // Assertions
        $this->assertEquals(1, $result);
    }

    /**
     * Test AmqpEventConsumer::computeAttempts()
     */
    public function testComputeAttemptsWhenRestarAttempsButNoTimeoutOccurred()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), [
            AmqpEventConsumer::OPTION_RESTART_ATTEMPTS => 5
        ]);

        // Run the test
        $result = $this->callPrivateMethod($eventConsumer, 'computeAttempts', [ false ]);

        // Assertions
        $this->assertEquals(5, $result);
    }

    /**
     * Test AmqpEventConsumer::computeAttempts()
     */
    public function testComputeAttemptsWhenRestarAttempsAndTimeoutOccurred()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), [
            AmqpEventConsumer::OPTION_RESTART_ATTEMPTS => 5
        ]);

        // Run the test
        $result = $this->callPrivateMethod($eventConsumer, 'computeAttempts', [ true ]);

        // Assertions
        $this->assertEquals(5, $result);
    }

    /**
     * Test AmqpEventConsumer::reconnect()
     */
    public function testReconnect()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        $channelMock = $this->createChannelMock();

        $connectionMock
            ->expects($this->once())
            ->method('reconnect');

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
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), []);

        // Run the test
        $result = $this->callPrivateMethod($eventConsumer, 'reconnect', [ 'resource', 1 ]);

        // Assertions
        $this->assertEquals($channelMock, $result);
    }

    /**
     * Test AmqpEventConsumer::reconnect()
     */
    public function testReconnectWhenErrorAndNoMoreReconnectionAttempts()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();

        $connectionMock
            ->expects($this->once())
            ->method('reconnect')
            ->willThrowException(new AMQPRuntimeException());


        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), []);

        // Run the test
        $result = $this->callPrivateMethod($eventConsumer, 'reconnect', [ 'resource', 1 ]);

        // Assertions
        $this->assertNull($result);
    }

    /**
     * Test AmqpEventConsumer::reconnect()
     */
    public function testReconnectWhenErrorAndReconnectionAttemptsAndWaitTime()
    {
        // Mock's
        $connectionMock = $this->createConnectionMock();


        $connectionMock
            ->expects($this->exactly(3))
            ->method('reconnect')
            ->willThrowException(new AMQPRuntimeException());


        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Create the AmqpEventConsumer object
        $eventConsumer = new AmqpEventConsumer($connection, new AmqpConfigurator(), [], new NullLogger(), [
            AmqpEventConsumer::OPTION_RESTART_WAIT_TIME => 1
        ]);

        // Run the test
        $result = $this->callPrivateMethod($eventConsumer, 'reconnect', [ 'resource', 3 ]);

        // Assertions
        $this->assertNull($result);
    }
}
