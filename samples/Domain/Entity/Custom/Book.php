<?php

namespace Samples\Domain\Entity\Custom;

use Davamigo\Domain\Core\Serializable\Serializable;
use Samples\Domain\Entity\Book as BaseBook;

/**
 * Entity Book with custom serialization for sampling purposes
 *
 * @package Samples\Domain\Entity\Custom
 * @author davamigo@gmail.com
 */
class Book extends BaseBook
{
    /**
     * Creates a serializable object from an array
     *
     * @param array $data
     * @return Serializable
     */
    public static function create(array $data) : Serializable
    {
        $releaseDate = $data['releaseDate'] ?? null;

        return new self(
            $data['uuid'] ?? null,
            $data['name'] ?? null,
            Publisher::create($data['publisher'] ?? []),
            $releaseDate ? \DateTime::createFromFormat(\DateTime::RFC3339, $releaseDate) : null,
            array_map(
                function (array $author) {
                    return Author::create($author);
                },
                $data['authors'] ?? []
            )
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
            'uuid'          => $this->uuid()->toString(),
            'name'          => $this->name(),
            'publisher'     => $this->publisher()->serialize(),
            'releaseDate'   => $this->releaseDate()->format(\DateTime::RFC3339),
            'authors'       => array_map(
                function (Author $author) {
                    return $author->serialize();
                },
                $this->authors()
            )
        ];
    }
}
