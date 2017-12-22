<?php

namespace Test\Unit\Infrastructure\Core\Amqp;

use Davamigo\Domain\Core\Event\EventBase;
use Davamigo\Domain\Core\Event\EventBusException;
use Davamigo\Domain\Core\Serializable\Serializable;
use Davamigo\Domain\Core\Serializable\SerializableTrait;
use Davamigo\Infrastructure\Core\Event\AmqpEventBus;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPIOException;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\NullLogger;

/**
 * Test of class AmqpEventBus
 *
 * @package Test\Unit\Infrastructure\Core\Amqp
 * @author davamigo@gmail.com
 *
 * @group Test_Unit_Infrastructure_Core_EventBus_Amqp
 * @group Test_Unit_Infrastructure_Core_EventBus
 * @group Test_Unit_Infrastructure_Core
 * @group Test_Unit_Infrastructure
 * @group Test_Unit
 * @group Test
 * @test
 */
class AmqpEventBusTest extends AmqpTestCase
{
    /**
     * Test publishEvent()
     */
    public function testPublishEventHappyPath()
    {
        $connectionMock = $this->createConnectionMock();

        $channelMock = $this->createChannelMock();

        $connectionMock
            ->expects($this->once())
            ->method('channel')
            ->willReturn($channelMock);

        $channelMock
            ->expects($this->once())
            ->method('tx_select');

        $channelMock
            ->expects($this->once())
            ->method('tx_commit');

        $channelMock
            ->expects($this->once())
            ->method('basic_publish');

        $channelMock
            ->expects($this->once())
            ->method('close');

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        $payload = new class implements Serializable {
            use SerializableTrait;
        };

        $event = new class ('name', $payload, 'topic', 'key') extends EventBase {
            use SerializableTrait;
        };

        // Create test object
        $eventBus = new AmqpEventBus($connection, new NullLogger());

        // Run test
        $eventBus->publishEvent($event);
    }

    /**
     * Test publishEvent()
     */
    public function testPublishEventWhenNoResource()
    {
        $connectionMock = $this->createConnectionMock();

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        $payload = new class implements Serializable {
            use SerializableTrait;
        };

        $event = new class ('name', $payload, '', 'key') extends EventBase {
            use SerializableTrait;
        };

        $this->expectException(EventBusException::class);

        // Create test object
        $eventBus = new AmqpEventBus($connection, new NullLogger());

        // Run test
        $eventBus->publishEvent($event);
    }

    /**
     * Test publishEvent()
     */
    public function testPublishEventWhenAmqpException()
    {
        $connectionMock = $this->createConnectionMock();

        $connectionMock
            ->expects($this->once())
            ->method('channel')
            ->willThrowException(new AMQPIOException());

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        $payload = new class implements Serializable {
            use SerializableTrait;
        };

        $event = new class ('name', $payload, 'topic', 'key') extends EventBase {
            use SerializableTrait;
        };

        $this->expectException(EventBusException::class);

        // Create test object
        $eventBus = new AmqpEventBus($connection, new NullLogger());

        // Run test
        $eventBus->publishEvent($event);
    }

    /**
     * Test getChannel()
     */
    public function testGetChannel()
    {
        $connectionMock = $this->createConnectionMock();

        $channelMock = $this->createChannelMock();

        $connectionMock
            ->expects($this->once())
            ->method('channel')
            ->willReturn($channelMock);

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        // Create test object
        $eventBus = new AmqpEventBus($connection, new NullLogger());

        // Run test
        $this->callPrivateMethod($eventBus, 'getChannel');
    }

    /**
     * Test closeChannel()
     */
    public function testCloseChannel()
    {
        $connectionMock = $this->createConnectionMock();

        $channelMock = $this->createChannelMock();

        $channelMock
            ->expects($this->once())
            ->method('close');

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        /** @var AMQPChannel $connection */
        $channel = $channelMock;

        // Create test object
        $eventBus = new AmqpEventBus($connection, new NullLogger());

        // Run test
        $this->callPrivateMethod($eventBus, 'closeChannel', [ $channel ]);
    }

    /**
     * Test createMessage()
     */
    public function testCreateMessage()
    {
        $connectionMock = $this->createConnectionMock();

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        $payload = new class implements Serializable {
            use SerializableTrait;
        };

        $event = new class ('the_name', $payload, 'a_topic', 'some_key') extends EventBase {
            use SerializableTrait;
        };

        $metadata = [
            'topic' => 'a_topic',
            'routingKey' => 'some_key'
        ];

        // Create test object
        $eventBus = new AmqpEventBus($connection, new NullLogger());

        // Run test
        $result = $this->callPrivateMethod($eventBus, 'createMessage', [ $event ]);

        // Assertions
        $this->assertInstanceOf(AMQPMessage::class, $result);

        /** @var AMQPMessage $result */
        $body = json_decode($result->body, true);
        $this->assertEquals($event->uuid()->toString(), $body['uuid']);
        $this->assertEquals('event', $body['type']);
        $this->assertEquals('the_name', $body['name']);
        $this->assertEquals($metadata, $body['metadata']);
        $this->assertEquals(2, $result->get('delivery_mode'));
        $this->assertEquals([], $body['payload']);
    }

    /**
     * Test encodeMessage()
     */
    public function testEncodeMessage()
    {
        $connectionMock = $this->createConnectionMock();

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        $data = [
            'f1' => 'v1',
            'f2' => 22,
            'f3' => [
                10,
                11,
                12
            ]
        ];

        $expected = '{"f1":"v1","f2":22,"f3":[10,11,12]}';


        // Create test object
        $eventBus = new AmqpEventBus($connection, new NullLogger());
        // Run test
        $result = $this->callPrivateMethod($eventBus, 'encodeMessage', [ $data ]);

        // Assertions
        $this->assertEquals($expected, $result);
    }

    /**
     * Test prepareMetatada()
     */
    public function testPrepareMetatadaRegularMode()
    {
        $connectionMock = $this->createConnectionMock();

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        $metadata = [
            'topic' => '_a_topic_',
            'routingKey' => '_a_key_'
        ];

        $expected = [
            'delivery_mode' => 2
        ];


        // Create test object
        $eventBus = new AmqpEventBus($connection, new NullLogger());

        // Run test
        $result = $this->callPrivateMethod($eventBus, 'prepareMetatada', [ $metadata ]);

        // Assertions
        $this->assertEquals($expected, $result);
    }

    /**
     * Test prepareMetatada()
     */
    public function testPrepareMetatadaChangingDeliveryMode()
    {
        $connectionMock = $this->createConnectionMock();

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        $metadata = [
            'delivery_mode' => 1,
            'another_topic' => 2
        ];

        $expected = [
            'delivery_mode' => 1,
            'another_topic' => 2
        ];


        // Create test object
        $eventBus = new AmqpEventBus($connection, new NullLogger());

        // Run test
        $result = $this->callPrivateMethod($eventBus, 'prepareMetatada', [ $metadata ]);

        // Assertions
        $this->assertEquals($expected, $result);
    }

    /**
     * Test publishMessage()
     */
    public function testPublishMessage()
    {
        $connectionMock = $this->createConnectionMock();

        $channelMock = $this->createChannelMock();

        $channelMock
            ->expects($this->once())
            ->method('basic_publish');

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        /** @var AMQPChannel $connection */
        $channel = $channelMock;

        // Create test object
        $eventBus = new AmqpEventBus($connection, new NullLogger());

        // Run test
        $this->callPrivateMethod($eventBus, 'publishMessage', [ $channel, new AMQPMessage(), 'res', 'key' ]);
    }

    /**
     * Test beginTransaction()
     */
    public function testBeginTransaction()
    {
        $connectionMock = $this->createConnectionMock();

        $channelMock = $this->createChannelMock();

        $channelMock
            ->expects($this->once())
            ->method('tx_select');

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        /** @var AMQPChannel $connection */
        $channel = $channelMock;

        // Create test object
        $eventBus = new AmqpEventBus($connection, new NullLogger());

        // Run test
        $this->callPrivateMethod($eventBus, 'beginTransaction', [ $channel ]);
    }

    /**
     * Test commitTransaction()
     */
    public function testCommitTransaction()
    {
        $connectionMock = $this->createConnectionMock();

        $channelMock = $this->createChannelMock();

        $channelMock
            ->expects($this->once())
            ->method('tx_commit');

        /** @var AMQPStreamConnection $connection */
        $connection = $connectionMock;

        /** @var AMQPChannel $connection */
        $channel = $channelMock;

        // Create test object
        $eventBus = new AmqpEventBus($connection, new NullLogger());

        // Run test
        $this->callPrivateMethod($eventBus, 'commitTransaction', [ $channel ]);
    }
}
