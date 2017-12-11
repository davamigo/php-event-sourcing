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
     * Starts consuming events from a queue.
     *
     * @param string   $resource  The name of the queue to consume.
     * @param Event    $baseEvent The base event object to unserialize the event data.
     * @param callable $callback  Callback func to call wen new event received.
     * @return $this
     * @throws EventConsumerException
     */
    public function start($resource, Event $baseEvent, callable $callback) : EventConsumer;

    /**
     * Stops consuming events from a queue.
     *
     * @return $this
     */
    public function stop() : EventConsumer;
}
