<?php

namespace Davamigo\Domain\Core;

/**
 * Interface Serializable
 *
 * @package Davamigo\Domain\Core
 * @author davamigo@gmail.com
 */
interface Serializable
{
    /**
     * Creates a serializable object from an array
     *
     * @param array $data
     * @return Serializable
     */
    public static function create(array $data) : Serializable;

    /**
     * Serializes the object to an array
     *
     * @return array
     */
    public function serialize() : array;
}
