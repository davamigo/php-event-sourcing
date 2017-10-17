<?php

namespace Davamigo\Domain\Core;

use Davamigo\Domain\Core\Exception\UuidException;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
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
     * @throws UuidException
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
     * @throws UuidException
     */
    public static function fromString(string $str) : Uuid
    {
        try {
            return new self(RamseyUuid::fromString($str));
        } catch (InvalidUuidStringException $exc) {
            throw new UuidException('Invalid UUID string: ' . $str, 0, $exc);
        }
    }

    /**
     * Converts the Uuid to a string
     *
     * @return string
     * @throws UuidException
     */
    public function toString() : string
    {
        return $this->rawData->toString();
    }
}
