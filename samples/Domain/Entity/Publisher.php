<?php

namespace Samples\Domain\Entity;

use Davamigo\Domain\Core\EntityObj;
use Davamigo\Domain\Core\SerializableTrait;

/**
 * Class Publisher
 *
 * @package Samples\Domain\Entity
 * @author davamigo@gmail.com
 */
class Publisher extends EntityObj
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
