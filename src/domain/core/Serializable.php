<?php

namespace Davamigo\domain\core;

/**
 * Interface Serializable
 *
 * @package Davamigo\domain\core
 * @author davamigo@gmail.com
 */
interface Serializable
{
    /**
     * Creates a serializable object
     *
     * @param array $data
     * @return Serializable
     */
    public static function create(array $data) : Serializable;

    /**
     * Serializes the object
     *
     * @return array
     */
    public function serialize() : array;
}
