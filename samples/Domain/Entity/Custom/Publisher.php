<?php

namespace Samples\Domain\Entity\Custom;

use Davamigo\Domain\Core\Serializable\Serializable;
use Samples\Domain\Entity\Publisher as BasePublisher;

/**
 * Entity Publisher with custom serialization for sampling purposes
 *
 * @package Samples\Domain\Entity\Custom
 * @author davamigo@gmail.com
 */
class Publisher extends BasePublisher
{
    /**
     * Creates a serializable object from an array
     *
     * @param array $data
     * @return Serializable
     */
    public static function create(array $data) : Serializable
    {
        return new self(
            $data['uuid'] ?? null,
            $data['name'] ?? null
        );
    }

    /**
     * Serializes the object to an array
     *
     * @return array
     */
    public function serialize() : array
    {
        return [
            'uuid' => $this->uuid()->toString(),
            'name' => $this->name()
        ];
    }
}
