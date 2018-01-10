<?php

namespace Davamigo\Domain\Core\Event;

/**
 * Collection of events
 *
 * @package Davamigo\Domain\Core\Event
 * @author davamigo@gmail.com
 */
class EventCollection
{
    /** @var Event[] */
    protected $events;

    /**
     * EventCollection constructor.
     *
     * @param iterable $events
     * @throws EventException
     */
    public function __construct(iterable $events)
    {
        foreach ($events as $event) {
            if (!$event instanceof Event) {
                throw new EventException('The items must be an instance of ' . Event::class);
            }
            $this->events[] = $event;
        }
    }

    /**
     * Gests the event collection as an array
     *
     * @return array
     */
    public function toArray() : array
    {
        return $this->events;
    }
}
