<?php

namespace Davamigo\Infrastructure\Config;

/**
 * Configuration class for AMQP queuing systems like RabbitMQ
 *
 * @package Davamigo\Infrastructure\Config
 * @author davamigo@gmail.com
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
}
