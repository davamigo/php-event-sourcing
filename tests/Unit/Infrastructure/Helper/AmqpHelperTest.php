<?php

namespace Test\Unit\Infrastructure\Helper;

use Davamigo\Infrastructure\Helper\AmqpHelper;
use PhpAmqpLib\Channel\AMQPChannel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test of class Davamigo\Infrastructure\Helper\AmqpHelper
 *
 * @package Test\Unit\Infrastructure\Helper
 * @author davamigo@gmail.com
 *
 * @group Test_Unit_Infrastructure_Helper_Amqp
 * @group Test_Unit_Infrastructure_Helper
 * @group Test_Unit_Infrastructure
 * @group Test_Unit
 * @group Test
 * @test
 */
class AmqpHelperTest extends TestCase
{
    /**
     * AmqpHelper::getDefaultExchange()
     */
    public function testBindExchangeAndQueueWithoutQueue()
    {
        $channelMock = $this->createChannelMock();

        $channelMock
            ->expects($this->once())
            ->method('exchange_declare')
            ->with('exchange1');

        $channelMock
            ->expects($this->never())
            ->method('queue_declare');

        $channelMock
            ->expects($this->never())
            ->method('queue_bind');

        AmqpHelper::bindExchangeAndQueue($channelMock, 'exchange1');
    }

    /**
     * AmqpHelper::getDefaultExchange()
     */
    public function testBindExchangeAndQueueWithOneQueue()
    {
        $channelMock = $this->createChannelMock();

        $channelMock
            ->expects($this->once())
            ->method('exchange_declare')
            ->with('exchange1');

        $channelMock
            ->expects($this->once())
            ->method('queue_declare')
            ->with('queue1');

        $channelMock
            ->expects($this->once())
            ->method('queue_bind')
            ->with('queue1', 'exchange1');

        AmqpHelper::bindExchangeAndQueue($channelMock, 'exchange1', [ 'queue1' ]);
    }

    /**
     * AmqpHelper::getDefaultExchange()
     */
    public function testBindExchangeAndQueueWithTwoQueues()
    {
        $channelMock = $this->createChannelMock();

        $channelMock
            ->expects($this->once())
            ->method('exchange_declare')
            ->with('exchange1');

        $channelMock
            ->expects($this->exactly(2))
            ->method('queue_declare');

        $channelMock
            ->expects($this->exactly(2))
            ->method('queue_bind');

        AmqpHelper::bindExchangeAndQueue($channelMock, 'exchange1', [ 'queue1', 'queue2' ]);
    }

    /**
     * AmqpHelper::getDefaultExchange()
     */
    public function testBindExchangeAndQueueWithoutOptions()
    {
        $channelMock = $this->createChannelMock();

        $channelMock
            ->expects($this->once())
            ->method('exchange_declare')
            ->with('exchange1', 'fanout', false, true, false);

        $channelMock
            ->expects($this->once())
            ->method('queue_declare')
            ->with('queue1', false, true, false, false);

        AmqpHelper::bindExchangeAndQueue($channelMock, 'exchange1', [ 'queue1' ], []);
    }

    /**
     * AmqpHelper::getDefaultExchange()
     */
    public function testBindExchangeAndQueueWithOptions()
    {
        $channelMock = $this->createChannelMock();

        $channelMock
            ->expects($this->once())
            ->method('exchange_declare')
            ->with('exchange1', 'type1', true, false, true);

        $channelMock
            ->expects($this->once())
            ->method('queue_declare')
            ->with('queue1', true, false, true, true);

        $options = [
            AmqpHelper::OPTION_EXCHANGE_TYPE => 'type1',
            AmqpHelper::OPTION_PASSIVE       => true,
            AmqpHelper::OPTION_DURABLE       => false,
            AmqpHelper::OPTION_AUTO_DELETE   => true,
            AmqpHelper::OPTION_EXCLUSIVE     => true
        ];

        AmqpHelper::bindExchangeAndQueue($channelMock, 'exchange1', [ 'queue1' ], $options);
    }

    /**
     * @return AMQPChannel|MockObject
     */
    protected function createChannelMock()
    {
        return $this
            ->getMockBuilder(AMQPChannel::class)
            ->disableOriginalConstructor()
            ->setMethods(['exchange_declare', 'queue_declare', 'queue_bind'])
            ->getMock();
    }
}
