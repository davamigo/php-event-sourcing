<?php

namespace Test\Unit\Infrastructure\Core;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Base class to create test for classes which use AMQP mocks
 *
 * @package Test\Unit\Infrastructure\Core
 * @author davamigo@gmail.com
 */
class AmqpTestCase extends AdvancedTestCase
{
    /**
     * Create connection mock object
     *
     * @return MockObject
     */
    protected function createConnectionMock()
    {
        return $this
            ->getMockBuilder(AMQPStreamConnection::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'channel',
                'reconnect'
            ])
            ->getMock();
    }

    /**
     * Create channel mock object
     *
     * @return MockObject
     */
    protected function createChannelMock()
    {
        return $this
            ->getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'exchange_declare',
                'queue_declare',
                'queue_bind',
                'close',
                'basic_publish',
                'tx_select',
                'tx_commit',
                'basic_qos',
                'basic_consume',
                'wait',
                'basic_ack',
                'basic_reject'
            ])
            ->getMock();
    }
}
