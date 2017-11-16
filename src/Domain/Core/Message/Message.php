<?php

namespace Davamigo\Domain\Core\Message;

use Davamigo\Domain\Core\Serializable\Serializable;
use Davamigo\Domain\Core\Uuid\Uuid;

/**
 * Interface for a message - A message is a serializable object
 *
 * @package Davamigo\Domain\Core\Message
 * @author davamigo@gmail.com
 */
interface Message extends Serializable
{
    /**
     * Constants types of messages
     */
    const TYPE_EVENT = 'event';
    const TYPE_COMMAND = 'command';

    /**
     * Return the Uuid of the entity
     *
     * @return Uuid
     */
    public function uuid() : Uuid;

    /**
     * Return the type of the message: command, event, ...
     *
     * @return string
     */
    public function type() : string;

    /**
     * Return the name of the message
     *
     * @return string
     */
    public function name() : string;

    /**
     * Return the creation date and time
     *
     * @return \DateTime
     */
    public function createdAt() : \DateTime;

    /**
     * Return the metadata of the message
     *
     * @return array
     */
    public function metadata() : array;
}
