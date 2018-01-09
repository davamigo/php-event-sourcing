<?php

namespace Davamigo\Domain\Core\EventStorage;

use Davamigo\Domain\Core\Event\Event;

/**
 * Interface for an event storer: The warehouse of the events
 *
 * @package Davamigo\Domain\Core\EventStorage
 * @author davamigo@gmail.com
 */
interface EventStorage
{
    /**
     * Stores the event in the event storage
     *
     * @param Event $event
     * @return EventStorage
     * @throws EventStorageException
     */
    public function storeEvent(Event $event) : EventStorage;
}
