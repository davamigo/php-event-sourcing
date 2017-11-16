<?php

namespace Domain\Core\Message;

use Davamigo\Domain\Core\Message\Message;
use Davamigo\Domain\Core\Serializable\Serializable;
use Davamigo\Domain\Core\Uuid\Uuid;
use Davamigo\Domain\Core\Uuid\UuidObj;

/**
 * Base abstract class for a message
 *
 * @package Domain\Core\Message
 * @author davamigo@gmail.com
 */
class MessageBase implements Message
{
    /** @var Uuid */
    private $uuid;

    /** @var string */
    private $type;

    /** @var string */
    private $name;

    /** @var \DateTime */
    private $createdAt;

    /** @var array */
    private $metadata;

    /**
     * Return the Uuid of the entity
     *
     * @return Uuid
     */
    public function uuid() : Uuid
    {
        return $this->uuid;
    }

    /**
     * Return the type of the message: command, event, ...
     *
     * @return string
     */
    public function type() : string
    {
        return $this->type;
    }

    /**
     * Return the name of the message
     *
     * @return string
     */
    public function name() : string
    {
        return $this->name;
    }

    /**
     * Return the creation date and time
     *
     * @return \DateTime
     */
    public function createdAt() : \DateTime
    {
        return $this->createdAt;
    }

    /**
     * Return the metadata of the message
     *
     * @return array
     */
    public function metadata() : array
    {
        return $this->metadata;
    }

    /**
     * MessageBase constructor.
     *
     * @param Uuid $uuid
     * @param string $type
     * @param string $name
     * @param \DateTime $createdAt
     * @param array $metadata
     */
    public function __construct(
        Uuid $uuid = null,
        string $type = null,
        string $name = null,
        \DateTime $createdAt = null,
        array $metadata = []
    ) {
        $this->uuid = $uuid ?: UuidObj::create();
        $this->type = $type;
        $this->name = $name;
        $this->createdAt = $createdAt ?: new \DateTime();
        $this->metadata = $metadata;
    }

    /**
     * Creates a serializable object from an array
     *
     * @param array $data
     * @return Serializable
     */
    public static function create(array $data) : Serializable
    {
        return new static(
            $data['uuid'] ?? null,
            $data['type'] ?? null,
            $data['name'] ?? null,
            $data['createdAt'] ?? null,
            $data['metadata'] ?? []
        );
    }

    /**
     * Serializes the object to an array
     *
     * @return array
     */
    public function serialize() : array
    {
        return [
            'uuid'      => $this->uuid->toString(),
            'type'      => $this->type,
            'name'      => $this->name,
            'createdAt' => $this->createdAt->format(\DateTime::RFC3339),
            'metadata'  => $this->metadata
        ];
    }
}
