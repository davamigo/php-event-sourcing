<?php

namespace Samples\Domain\Entity;

use Davamigo\Domain\Core\Entity\EntityBase;
use Davamigo\Domain\Core\Serializable\SerializableTrait;
use Davamigo\Domain\Core\Uuid\Uuid;

/**
 * Entity Book
 *
 * @package Samples\Domain\Entity
 * @author davamigo@gmail.com
 */
class Book extends EntityBase
{
    /** @var string */
    private $name;

    /** @var Publisher */
    private $publisher;

    /** @var Author[] */
    private $authors;

    /** @var \DateTime */
    private $releaseDate;

    /**
     * Book constructor.
     *
     * @param Uuid $uuid
     * @param string $name
     * @param Publisher $publisher
     * @param Author[] $authors
     * @param \DateTime $releaseDate
     */
    public function __construct(
        Uuid $uuid = null,
        string $name = null,
        Publisher $publisher = null,
        array $authors = [],
        \DateTime $releaseDate = null
    ) {
        parent::__construct($uuid);
        $this->name = $name;
        $this->publisher = $publisher ?: new Publisher();
        $this->authors = $authors;
        $this->releaseDate = $releaseDate ?: new \DateTime();
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return Publisher
     */
    public function publisher()
    {
        return $this->publisher;
    }

    /**
     * @return Author[]
     */
    public function authors()
    {
        return $this->authors;
    }

    /**
     * @return \DateTime
     */
    public function releaseDate()
    {
        return $this->releaseDate;
    }

    /**
     * @method create
     * @method serialize
     */
    use SerializableTrait;
}
