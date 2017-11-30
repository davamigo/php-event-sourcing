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
     * Return the payload of the event
     *
     * @return Serializable
     */
    public function payload();
}
