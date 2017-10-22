<?php

namespace Davamigo\Domain\Core\Entity;

use Davamigo\Domain\Core\Serializable\Serializable;
use Davamigo\Domain\Core\Uuid\Uuid;
use Davamigo\Domain\Core\Uuid\UuidException;
use Davamigo\Domain\Core\Uuid\UuidObj;

/**
 * Abstract class for an entity object. An entity always has an uuid.
 *
 * @package Davamigo\Domain\Core
 * @author davamigo@gmail.com
 */
abstract class EntityBase implements Entity
{
    /** @var Uuid */
    private $uuid;

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
     * EntityObj constructor
     *
     * @param Uuid|string|null $uuid
     * @throws EntityException
     */
    public function __construct($uuid = null)
    {
        if (null === $uuid) {
            $this->uuid = UuidObj::create();
        } elseif (is_string($uuid)) {
            try {
                $this->uuid = UuidObj::fromString($uuid);
            } catch (UuidException $exc) {
                throw new EntityException('Error paring an UUID: ' . $uuid, 0, $exc);
            }
        } elseif (!$uuid instanceof Uuid) {
            throw new EntityException('Invalid UUID received: ' . get_class($uuid));
        } else {
            $this->uuid = $uuid;
        }
    }

    /**
     * Creates a serializable object from an array
     *
     * @param array $data
     * @return Serializable
     */
    abstract public static function create(array $data) : Serializable;

    /**
     * Serializes the object to an array
     *
     * @return array
     */
    abstract public function serialize() : array;
}
