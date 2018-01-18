<?php

namespace Samples\Domain\Entity;

use Davamigo\Domain\Core\Entity\EntityBase;
use Davamigo\Domain\Core\Uuid\Uuid;

/**
 * Abstract entity Author for sampling purposes
 *
 * @package Samples\Domain\Entity
 * @author davamigo@gmail.com
 */
abstract class Author extends EntityBase
{
    /** @var string */
    private $firstName;

    /** @var string */
    private $lastName;

    /**
     * @return string
     */
    public function firstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function lastName()
    {
        return $this->lastName;
    }

    /**
     * Author constructor
     *
     * @param Uuid|string $uuid
     * @param string $firstName
     * @param string $lastName
     */
    public function __construct(
        $uuid = null,
        string $firstName = null,
        string $lastName = null
    ) {
        parent::__construct($uuid);
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }
}
