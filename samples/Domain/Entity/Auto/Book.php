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
        $publisher = $publisher ?: new Publisher();
        $releaseDate = $releaseDate ?: new \DateTime();
        $authors = !empty($authors) ? $authors : [ new Author() ];
        parent::__construct($uuid, $name, $publisher, $releaseDate, $authors);
    }

    /**
     * @method create
     * @method serialize
     */
    use SerializableTrait;
}
