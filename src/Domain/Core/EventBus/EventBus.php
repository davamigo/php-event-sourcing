<?php

namespace Davamigo\Domain\Core\EventBus;

use Davamigo\Domain\Core\Event\Event;

/**
 * Interface for an event bus, which dispatches the event (usually to a queue).
 *
 * @package Davamigo\Domain\Core\EventBus
 * @author davamigo@gmail.com
 */
interface EventBus
{
    /**
     * Publishes an event to the event bus.
     *
     * @param Event $event
     * @return $this
     * @throws EventBusException
     */
    public function publishEvent(Event $event) : EventBus;
}
