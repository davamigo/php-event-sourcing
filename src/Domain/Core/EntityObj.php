<?php

namespace Davamigo\Domain\Core;

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
     * @param Uuid|null $uuid
     */
    public function __construct(Uuid $uuid = null)
    {
        $this->uuid = $uuid ?: UuidObj::create();
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
