<?php

namespace Davamigo\Domain\Core\Entity;

use Davamigo\Domain\Core\Serializable\Serializable;
use Davamigo\Domain\Core\Uuid\Uuid;

/**
 * Interface for an entity. An entity always has an uuid.
 *
 * @package Davamigo\Domain\Core\Entity
 * @author davamigo@gmail.com
 */
interface Entity extends Serializable
{
    /**
     * Return the Uuid of the entity
     *
     * @return Uuid
     */
    public function uuid() : Uuid;
}
