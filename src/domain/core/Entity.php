<?php

namespace Davamigo\domain\core;

/**
 * Interface Entity
 *
 * @package Davamigo\domain\core
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
