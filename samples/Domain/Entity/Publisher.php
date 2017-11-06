<?php

namespace Samples\Domain\Entity;

use Davamigo\Domain\Core\Entity\EntityBase;
use Davamigo\Domain\Core\Uuid\Uuid;

/**
 * Abstract entity Publisher for sampling purposes
 *
 * @package Samples\Domain\Entity
 * @author davamigo@gmail.com
 */
abstract class Publisher extends EntityBase
{
    /** @var string */
    private $name;

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Publisher constructor
     *
     * @param Uuid|string $uuid
     * @param string $name
     */
    public function __construct($uuid = null, string $name = null)
    {
        parent::__construct($uuid);
        $this->name = $name;
    }
}
