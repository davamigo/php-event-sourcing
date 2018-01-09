<?php

namespace Davamigo\Infrastructure\Core\Helpers;

use PhpAmqpLib\Channel\AMQPChannel;

/**
 * Cconfiguration class for AMQP queuing systems like RabbitMQ
 *
 * @package Davamigo\Infrastructure\Core\Helpers
 */
class AmqpConfigurator
{
    /**
     * Returns the default exchange
     *
     * @return string
     */
    public function getDefaultExchange() : string
    {
        return 'app.events';
    }

    /**
     * Returns the default queues
     *
     * @return string[]
     */
    public function getDefaultQueues() : array
    {
        return [
            'app.events.storage',
            'app.events.model'
        ];
    }

    /**
     * Declares the exchange and the queue and binds them
     *
     * @param AMQPChannel $channel
     * @param string      $exchange
     * @param string[]    $queues
     * @return void
     */
    final public function bindExchangeAndQueue(AMQPChannel $channel, string $exchange, array $queues = []) : void
    {
        $channel->exchange_declare($exchange, 'fanout', false, true, false);
        foreach ($queues as $queue) {
            $channel->queue_declare($queue, false, true, false, false);
            $channel->queue_bind($queue, $exchange);
        }
    }
}
