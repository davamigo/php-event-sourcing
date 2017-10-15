<?php

namespace Davamigo\domain\core;

/**
 * Interface for an Uuid (Universal User Id)
 *
 * @package Davamigo\domain\core
 * @author davamigo@gmail.com
 */
interface Uuid
{
    /**
     * Creates a new Uuid
     *
     * @return Uuid
     */
    public static function create() : Uuid;

    /**
     * Creates an Uuid from a string
     *
     * @param string $str
     * @return Uuid
     */
    public static function fromString(string $str) : Uuid;

    /**
     * Converts the Uuid to a string
     *
     * @return string
     */
    public function toString() : string;
}