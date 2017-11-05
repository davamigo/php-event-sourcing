<?php

namespace Samples\Domain\Entity;

use Davamigo\Domain\Core\Entity\EntityBase;
use Davamigo\Domain\Core\Uuid\Uuid;

/**
 * Entity Book
 *
 * @package Samples\Domain\Entity
 * @author davamigo@gmail.com
 */
abstract class Book extends EntityBase
{
    /** @var string */
    private $name;

    /** @var Publisher */
    private $publisher;

    /** @var \DateTime */
    private $releaseDate;

    /** @var Author[] */
    private $authors;

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
     * @return \DateTime
     */
    public function releaseDate()
    {
        return $this->releaseDate;
    }

    /**
     * @return Author[]
     */
    public function authors()
    {
        return $this->authors;
    }

    /**
     * Book constructor.
     *
     * @param Uuid $uuid
     * @param string $name
     * @param Publisher $publisher
     * @param \DateTime $releaseDate
     * @param Author[] $authors
     */
    public function __construct(
        Uuid $uuid = null,
        string $name = null,
        Publisher $publisher = null,
        \DateTime $releaseDate = null,
        array $authors = []
    ) {
        parent::__construct($uuid);
        $this->name = $name;
        $this->publisher = $publisher ?: null;
        $this->releaseDate = $releaseDate ?: new \DateTime();
        $this->authors = $authors;
    }
}
