<?php

namespace Davamigo\Domain\Core;

use Davamigo\Domain\Core\Exception\UuidException;

/**
 * Interface for an Uuid (Universal User Id)
 *
 * @package Davamigo\Domain\Core
 * @author davamigo@gmail.com
 */
interface Uuid
{
    /**
     * Creates a new Uuid
     *
     * @return Uuid
     * @throws UuidException
     */
    public static function create() : Uuid;

    /**
     * Creates an Uuid from a string
     *
     * @param string $str
     * @return Uuid
     * @throws UuidException
     */
    public static function fromString(string $str) : Uuid;

    /**
     * Converts the Uuid to a string
     *
     * @return string
     * @throws UuidException
     */
    public function toString() : string;
}
