<?php

namespace Davamigo\Domain\Core\EventConsumer;

/**
 * Interface for an event consumer, which reads events from a queue.
 *
 * @package Davamigo\Domain\Core\EventConsumer
 * @author davamigo@gmail.com
 */
interface EventConsumer
{
    /**
     * Starts listening for events from a queue or topic.
     *
     * @param string   $topic    The topic to consume (usually the name of the queue).
     * @param callable $callback Callback func to call wen new event received.
     * @return $this
     * @throws EventConsumerException
     */
    public function listen($topic, callable $callback) : EventConsumer;

    /**
     * Stops listening events.
     *
     * @return $this
     */
    public function stop() : EventConsumer;
}
