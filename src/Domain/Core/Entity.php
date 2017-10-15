<?php

namespace Davamigo\Domain\Core;

/**
 * Interface for an entity - Every entity has an uuid.
 *
 * @package Davamigo\Domain\Core
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
