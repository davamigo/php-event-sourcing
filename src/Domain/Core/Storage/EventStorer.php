<?php

namespace Davamigo\Domain\Core\Storage;
use Davamigo\Domain\Core\Event\Event;

/**
 * Interface for an event storer: The warehouse of the events
 *
 * @package Davamigo\Domain\Core\Storag
 * @author davamigo@gmail.com
 */
interface EventStorer
{
    /**
     * Stores the event in the event storage
     *
     * @param Event $event
     * @return EventStorer
     * @throws EventStorerException
     */
    public function storeEvent(Event $event) : EventStorer;
}
