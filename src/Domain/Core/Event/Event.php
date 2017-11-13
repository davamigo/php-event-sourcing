<?php

namespace Davamigo\Domain\Core\Event;

use Davamigo\Domain\Core\Message\Message;

/**
 * Interface for an event - An event is a message with some payload
 *
 * @package Davamigo\Domain\Core\Event
 * @author davamigo@gmail.com
 */
interface Event extends Message
{
    /**
     * Return the content of the event
     *
     * @return mixed
     */
    public function payload();
}
