<?php

namespace Davamigo\Domain\Core\EventHandler;

use Davamigo\Domain\Core\Event\Event;

/**
 * Interface for an event handler who handles an event received
 *
 * @package Davamigo\Domain\Core\EventHandler
 * @author davamigo@gmail.com
 */
interface EventHandler
{
    /**
     * Habdles the event
     *
     * @param Event $event
     * @return EventHandler
     * @throws EventHandlerException
     */
    public function handleEvent(Event $event) : EventHandler;
}
