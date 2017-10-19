<?php

namespace Samples\Domain\Entity;

use Davamigo\Domain\Core\Entity\EntityBase;
use Davamigo\Domain\Core\Serializable\SerializableTrait;

/**
 * Class Publisher
 *
 * @package Samples\Domain\Entity
 * @author davamigo@gmail.com
 */
class Publisher extends EntityBase
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
     * @method create
     * @method serialize
     */
    use SerializableTrait;
}
