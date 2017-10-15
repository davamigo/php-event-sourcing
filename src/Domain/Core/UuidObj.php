<?php

namespace Davamigo\Domain\Core;

use Ramsey\Uuid\Uuid as RamseyUuid;
use Ramsey\Uuid\UuidInterface as RamseyUuidInterface;

/**
 * Class to manage Uuids (Universal User Id)
 *
 * @package Davamigo\Domain\Core
 * @author davamigo@gmail.com
 */
class UuidObj implements Uuid
{
    /** @var RamseyUuidInterface */
    private $rawData;

    /**
     * Uuid constructor
     *
     * @param RamseyUuidInterface $rawData
     */
    private function __construct($rawData)
    {
        $this->rawData = $rawData;
    }

    /**
     * Creates a new Uuid
     *
     * @return Uuid
     */
    public static function create() : Uuid
    {
        return new self(RamseyUuid::uuid1());
    }

    /**
     * Creates an Uuid from a string
     *
     * @param string $str
     * @return Uuid
     */
    public static function fromString(string $str) : Uuid
    {
        return new self(RamseyUuid::fromString($str));
    }

    /**
     * Converts the Uuid to a string
     *
     * @return string
     */
    public function toString() : string
    {
        return $this->rawData->toString();
    }
}
