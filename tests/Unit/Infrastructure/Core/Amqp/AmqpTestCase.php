<?php

namespace Test\Unit\Infrastructure\Core\Amqp;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class AmqpBaseTest
 *
 * @package Test\Unit\Infrastructure\Core\Amqp
 * @author davamigo@gmail.com
 */
class AmqpTestCase extends TestCase
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

    /**
     * Call private method
     *
     * @param object $object
     * @param string $method
     * @param array $args
     * @return mixed
     */
    protected function callPrivateMethod($object, string $method, array $args = [])
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionMethod = $reflectionClass->getMethod($method);
        $reflectionMethod->setAccessible(true);
        return $reflectionMethod->invokeArgs($object, $args);
    }

    /**
     * Get the value of a private property of an object using reflection
     *
     * @param object $object
     * @param string $property
     * @return mixed
     */
    protected function getPrivateProperty($object, string $property)
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($property);
        $reflectionProperty->setAccessible(true);
        return $reflectionProperty->getValue($object);
    }

    /**
     * Get the value of a private property of an object using reflection
     *
     * @param object $object
     * @param string $property
     * @param mixed  $value
     * @return void
     */
    protected function setPrivateProperty($object, string $property, $value) : void
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
    }
}
