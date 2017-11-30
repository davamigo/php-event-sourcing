<?php

namespace Davamigo\Domain\Core\Event;

use Davamigo\Domain\Core\Message\Message;
use Davamigo\Domain\Core\Message\MessageBase;
use Davamigo\Domain\Core\Message\MessageException;
use Davamigo\Domain\Core\Serializable\Serializable;
use Davamigo\Domain\Core\Uuid\Uuid;

/**
 *  Abstract class for an event object. An event is a message with some payload.
 *
 * @package Davamigo\Domain\Core\Event
 * @author davamigo@gmail.com
 */
abstract class EventBase extends MessageBase implements Event
{
    /** @var Serializable */
    private $payload;

    /**
     * EventBase constructor.
     *
     * @param string           $name
     * @param Serializable     $payload,
     * @param Uuid|string|null $uuid
     * @param \DateTime        $createdAt
     * @param array            $metadata
     */
    public function __construct(
        string $name,
        Serializable $payload,
        $uuid = null,
        \DateTime $createdAt = null,
        array $metadata = []
    ) {
        try {
            parent::__construct(Message::TYPE_EVENT, $name, $uuid, $createdAt, $metadata);
        } catch (MessageException $exc) {
            throw new EventException($exc->getMessage(), 0, $exc);
        }

        $this->payload = $payload;
    }

    /**
     * Return the payload of the event
     *
     * @return Serializable
     */
    public function payload()
    {
        return $this->payload;
    }
}
