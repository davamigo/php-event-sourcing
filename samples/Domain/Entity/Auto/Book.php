<?php

namespace Samples\Domain\Entity\Auto;

use Davamigo\Domain\Core\Serializable\SerializableTrait;
use Davamigo\Domain\Core\Uuid\Uuid;
use Samples\Domain\Entity\Book as BaseBook;

/**
 * Entity Book with auto serialization for sampling purposes
 *
 * @package Samples\Domain\Entity\Auto
 * @author davamigo@gmail.com
 */
class Book extends BaseBook
{
    /**
     * Book constructor.
     *
     * @param Uuid|string $uuid
     * @param string $name
     * @param Publisher $publisher
     * @param \DateTime $releaseDate
     * @param Author[] $authors
     */
    public function __construct(
        $uuid = null,
        string $name = null,
        Publisher $publisher = null,
        \DateTime $releaseDate = null,
        array $authors = []
    ) {
        parent::__construct(
            $uuid,
            $name,
            $publisher ?: new Publisher(),
            $releaseDate  ?: new \DateTime(),
            !empty($authors) ? $authors : [ new Author() ]
        );
    }

    /**
     * @method create
     * @method serialize
     */
    use SerializableTrait;
}
