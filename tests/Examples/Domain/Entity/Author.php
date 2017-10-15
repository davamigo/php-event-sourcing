<?php

namespace Test\Examples\Domain\Entity;

use Davamigo\Domain\Core\EntityObj;
use Davamigo\Domain\Core\Serializable;
use Davamigo\Domain\Core\Uuid;

/**
 * Entity Author for testing purposes
 *
 * @package Test\Examples\Domain\Entity
 * @author davamigo@gmail.com
 */
class Author extends EntityObj
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
     * @param Uuid $uuid
     * @param string $firstName
     * @param string $lastName
     */
    public function __construct(
        Uuid $uuid = null,
        string $firstName = null,
        string $lastName = null
    ) {
        parent::__construct();
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    /**
     * Creates a serializable object
     *
     * @param array $data
     * @return Serializable
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
            'uuid'      => $this->uuid(),
            'firstName' => $this->firstName(),
            'lastName'  => $this->lastName()
        ];
    }
}
