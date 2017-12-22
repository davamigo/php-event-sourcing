<?php

namespace Davamigo\Domain\Core\Message;

use Davamigo\Domain\Core\Uuid\Uuid;
use Davamigo\Domain\Core\Uuid\UuidException;
use Davamigo\Domain\Core\Uuid\UuidObj;

/**
 *  Abstract class for a message object. A message is a serializable object.
 *
 * @package Davamigo\Domain\Core\Message
 * @author davamigo@gmail.com
 */
abstract class MessageBase implements Message
{
    /** @var Uuid */
    protected $uuid;

    /** @var string */
    protected $type;

    /** @var string */
    protected $name;

    /** @var \DateTime */
    protected $createdAt;

    /** @var array */
    protected $metadata;

    /**
     * MessageBase constructor.
     *
     * @param string           $type
     * @param string           $name
     * @param Uuid|string|null $uuid
     * @param \DateTime|null   $createdAt
     * @param array            $metadata
     * @throws MessageException
     */
    public function __construct(
        string $type,
        string $name,
        $uuid = null,
        \DateTime $createdAt = null,
        array $metadata = []
    ) {
        if (empty($type)) {
            throw new MessageException('The message has to have a type.');
        }

        if (empty($name)) {
            throw new MessageException('The message has to have a name.');
        }

        try {
            $this->uuid = UuidObj::createNewUuid($uuid);
        } catch (UuidException $exc) {
            throw new MessageException($exc->getMessage(), 0, $exc);
        }

        $this->type = $type;
        $this->name = $name;
        $this->createdAt = $createdAt ?: new \DateTime();
        $this->metadata = $metadata;
    }

    /**
     * Return the Uuid of the entity
     *
     * @return Uuid
     */
    public function uuid(): Uuid
    {
        return $this->uuid;
    }

    /**
     * Return the type of the event
     *
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * Return the name of the event
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Return the creation date and time
     *
     * @return \DateTime
     */
    public function createdAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * Return the metadata of the event
     *
     * @return array
     */
    public function metadata(): array
    {
        return $this->metadata;
    }

    /**
     * Add metadata to the event
     *
     * @param array $metadata
     * @return MessageBase
     */
    public function addMetadata(array $metadata): MessageBase
    {
        foreach ($metadata as $key => $value) {
            if (is_numeric($key)) {
                $this->metadata[] = $value;
            } else {
                $this->metadata[$key] = $value;
            }
        }
        return $this;
    }
}
