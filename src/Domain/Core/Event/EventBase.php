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
    /** @var string */
    protected $action;

    /** @var Serializable */
    protected $payload;

    /**
     * EventBase constructor.
     *
     * @param string           $name
     * @param string           $action
     * @param Serializable     $payload,
     * @param array            $metadata
     * @param \DateTime|null   $createdAt
     * @param Uuid|string|null $uuid
     */
    public function __construct(
        string $name,
        string $action,
        Serializable $payload,
        array $metadata = [],
        \DateTime $createdAt = null,
        $uuid = null
    ) {
        try {
            parent::__construct(Message::TYPE_EVENT, $name, $metadata, $createdAt, $uuid);
        } catch (MessageException $exc) {
            throw new EventException($exc->getMessage(), 0, $exc);
        }

        $this->action = $action;
        $this->payload = clone $payload;
    }

    /**
     * Returns the action of the event: insert/update/delete
     *
     * @return string
     */
    public function action() : string
    {
        return $this->action;
    }

    /**
     * Returns the payload of the event which is a serializable object.
     *
     * @return Serializable
     */
    public function payload()
    {
        return $this->payload;
    }

    /**
     * Returns the topic of the event. Usually the name of the queue or the exchange.
     *
     * @return string|null
     */
    public function topic()
    {
        return $this->metadata['topic'] ?? null;
    }

    /**
     * Sets the topic for the event. Usually the name of the queue or the exchange.
     *
     * @param string $topic
     * @return EventBase
     */
    public function setTopic(string $topic = null) : EventBase
    {
        $this->metadata['topic'] = $topic;
        return $this;
    }

    /**
     * Returns the optional routing Key of the event (used to enroute the event to the right queue).
     *
     * @return string|null
     */
    public function routingKey()
    {
        return $this->metadata['routing_key'] ?? null;
    }

    /**
     * Sets the routing Key of the event (used to enroute the event to the right queue).
     *
     * @param string $routingKey
     * @return EventBase
     */
    public function setRoutingKey(string $routingKey = null) : EventBase
    {
        $this->metadata['routing_key'] = $routingKey;
        return $this;
    }
}
