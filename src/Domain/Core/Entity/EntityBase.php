<?php

namespace Davamigo\Domain\Core\Entity;

use Davamigo\Domain\Core\Serializable\Serializable;

/**
 * Abstract class for an entity object. An entity always has an uuid.
 *
 * @package Davamigo\Domain\Core
 * @author davamigo@gmail.com
 */
abstract class EntityBase implements Entity
{
    /**
     * @method uuid()
     */
    use EntityTrait;

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
