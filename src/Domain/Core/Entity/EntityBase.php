<?php

namespace Davamigo\Domain\Core\Entity;

use Davamigo\Domain\Core\Serializable\Serializable;
use Davamigo\Domain\Core\Uuid\Uuid;
use Davamigo\Domain\Core\Uuid\UuidException;
use Davamigo\Domain\Core\Uuid\UuidObj;

/**
 * Abstract class for an entity object. An entity always has an uuid.
 *
 * @package Davamigo\Domain\Core\Entity
 * @author davamigo@gmail.com
 */
abstract class EntityBase implements Entity
{
    /** @var Uuid */
    private $uuid;

    /**
     * EntityBase constructor
     *
     * @param Uuid|string|null $uuid
     * @throws EntityException
     */
    public function __construct($uuid = null)
    {
        try {
            $this->uuid = UuidObj::createNewUuid($uuid);
        } catch (UuidException $exc) {
            throw new EntityException($exc->getMessage(), 0, $exc);
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
