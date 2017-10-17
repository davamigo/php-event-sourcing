<?php

namespace Davamigo\Domain\Core;

use Davamigo\Domain\Core\Exception\EntityException;
use Davamigo\Domain\Core\Exception\UuidException;

/**
 * Abstract class for an entity object. An entity always has an uuid.
 *
 * @package Davamigo\Domain\Core
 * @author davamigo@gmail.com
 */
abstract class EntityObj implements Entity
{
    /** @var Uuid */
    private $uuid;

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
     * Return the Uuid of the entity
     *
     * @return Uuid
     */
    public function uuid() : Uuid
    {
        return $this->uuid;
    }
}
