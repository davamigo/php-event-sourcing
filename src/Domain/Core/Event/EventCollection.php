<?php

namespace Davamigo\Domain\Core\Event;

/**
 * Collection of events
 *
 * @package Davamigo\Domain\Core\Event
 * @author davamigo@gmail.com
 */
class EventCollection implements \Iterator
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
        $this->events = [];
        foreach ($events as $name => $event) {
            if (!$event instanceof Event) {
                throw new EventException('The items must be an instance of ' . Event::class);
            }
            if (is_numeric($name)) {
                $name = get_class($event);
            }
            $this->events[$name] = $event;
        }
    }

    /**
     * Gets the event collection as an array
     *
     * @return array
     */
    public function toArray() : array
    {
        return $this->events;
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return current($this->events);
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        next($this->events);
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return key($this->events);
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return key($this->events) !== null;
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        reset($this->events);
    }
}
