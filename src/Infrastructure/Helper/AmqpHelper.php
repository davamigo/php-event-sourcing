<?php

namespace Davamigo\Infrastructure\Helper;

use PhpAmqpLib\Channel\AMQPChannel;

/**
 * Helper class for AMQP queuing systems like RabbitMQ
 *
 * @package Davamigo\Infrastructure\Helper
 * @author davamigo@gmail.com
 */
class AmqpHelper
{
    const OPTION_EXCHANGE_TYPE = 'exchange_type';
    const DEFAULT_EXCHANGE_TYPE = 'fanout';

    const OPTION_PASSIVE = 'passive';
    const DEFAULT_PASSIVE = false;

    const OPTION_DURABLE = 'durable';
    const DEFAULT_DURABLE = true;

    const OPTION_AUTO_DELETE = 'auto_delete';
    const DEFAULT_AUTO_DELETE = false;

    const OPTION_EXCLUSIVE = 'exclusive';
    const DEFAULT_EXCLUSIVE = false;

    /**
     * Declares the exchange and the queue and binds them
     *
     * @param AMQPChannel $channel
     * @param string      $exchangeName
     * @param string[]    $queues
     * @param array       $options
     * @return void
     */
    final public static function bindExchangeAndQueue(
        AMQPChannel $channel,
        string $exchangeName,
        array $queues = [],
        array $options = []
    ) : void {
        $exchangeType = $options[self::OPTION_EXCHANGE_TYPE] ?? self::DEFAULT_EXCHANGE_TYPE;
        $passive = $options[self::OPTION_PASSIVE] ?? self::DEFAULT_PASSIVE;
        $durable = $options[self::OPTION_DURABLE] ?? self::DEFAULT_DURABLE;
        $autoDelete = $options[self::OPTION_AUTO_DELETE] ?? self::DEFAULT_AUTO_DELETE;
        $exclusive = $options[self::OPTION_EXCLUSIVE] ?? self::DEFAULT_EXCLUSIVE;

        $channel->exchange_declare($exchangeName, $exchangeType, $passive, $durable, $autoDelete);
        foreach ($queues as $queue) {
            $channel->queue_declare($queue, $passive, $durable, $exclusive, $autoDelete);
            $channel->queue_bind($queue, $exchangeName);
        }
    }
}
