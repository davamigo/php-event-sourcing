<?php

namespace Samples\Domain\Entity\Custom;

use Davamigo\Domain\Core\Serializable\Serializable;
use Samples\Domain\Entity\Author as BaseAuthor;

/**
 * Entity Author with custom serialization for sampling purposes
 *
 * @package Samples\Domain\Entity\Custom
 * @author davamigo@gmail.com
 */
class Author extends BaseAuthor
{
    /**
     * Creates a serializable object
     *
     * @param array $data
     * @return Serializable|Author
     */
    public static function create(array $data) : Serializable
    {
        return new self(
            $data['uuid'] ?? null,
            $data['firstName'] ?? null,
            $data['lastName'] ?? null
        );
    }

    /**
     * Serializes the object
     *
     * @return array
     */
    public function serialize() : array
    {
        return [
            'uuid'      => $this->uuid()->toString(),
            'firstName' => $this->firstName(),
            'lastName'  => $this->lastName()
        ];
    }
}
