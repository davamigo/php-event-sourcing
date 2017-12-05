<?php

namespace Davamigo\Domain\Core\Event;

use Davamigo\Domain\Core\Message\Message;
use Davamigo\Domain\Core\Serializable\Serializable;

/**
 * Interface for an event. An event is a message with some payload.
 *
 * An event represents something that took place in the domain. They are always named with a past-participle verb, such
 * as AuthorCreated. Since an event represents something in the past, it can be considered a statement of fact and used
 * to take decisions in other parts of the system.
 *
 * @package Davamigo\Domain\Core\Event
 * @author davamigo@gmail.com
 */
interface Event extends Message
{
    /**
     * Returns the payload of the event which is a serializable object.
     *
     * @return Serializable
     */
    public function payload();

    /**
     * Returns the topic of the event. Usually the name of the queue.
     *
     * @return string|null
     */
    public function topic();

    /**
     * Returns the optional routing Key of the event (used to enroute the event  to the right queue).
     *
     * @return string|null
     */
    public function routingKey();
}
