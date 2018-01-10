<?php

namespace Davamigo\Domain\Core\Command;

use Davamigo\Domain\Core\Message\Message;
use Davamigo\Domain\Core\Serializable\Serializable;

/**
 * Interface for a command. A command is a message with some payload.
 *
 * Commands are sent to change the domain. They are named with a verb in the imperative mode, for example createAuthor.
 * Unlike an event, a command is not a statement of fact; it's only a request, and thus may be refused by throwing an
 * exception.
 *
 * @package Davamigo\Domain\Core\Command
 * @author davamigo@gmail.com
 */
interface Command extends Message
{
    /**
     * Return the payload of the event
     *
     * @return Serializable
     */
    public function payload();
}
