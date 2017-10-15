<?php

namespace Davamigo\Domain\Core;

/**
 * Interface Entity
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
    public function getUuid() : Uuid;
}
