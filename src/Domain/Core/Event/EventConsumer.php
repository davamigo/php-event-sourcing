<?php

namespace Davamigo\Domain\Core\Event;

/**
 * Interface for an event consumer, which reads events from a queue.
 *
 * @package Davamigo\Domain\Core\Event
 * @author davamigo@gmail.com
 */
interface EventConsumer
{
    /**
     * Starts listening for events from a queue or topic.
     *
     * @param string   $resource  The name of the queue to consume.
     * @param callable $callback  Callback func to call wen new event received.
     * @return $this
     * @throws EventConsumerException
     */
    public function listen($resource, callable $callback) : EventConsumer;

    /**
     * Stops listening events.
     *
     * @return $this
     */
    public function stop() : EventConsumer;
}
