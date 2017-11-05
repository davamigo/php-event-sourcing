<?php

namespace Samples\Domain\Entity\Auto;

use Davamigo\Domain\Core\Serializable\SerializableTrait;
use Samples\Domain\Entity\Publisher as BasePublisher;

/**
 * Entity Publisher with auto serialization for sampling purposes
 *
 * @package Samples\Domain\Entity\Auto
 * @author davamigo@gmail.com
 */
class Publisher extends BasePublisher
{
    /**
     * @method create
     * @method serialize
     */
    use SerializableTrait;
}
