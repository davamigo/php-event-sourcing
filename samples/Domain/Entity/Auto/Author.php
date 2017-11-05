<?php

namespace Samples\Domain\Entity\Auto;

use Davamigo\Domain\Core\Serializable\SerializableTrait;
use Samples\Domain\Entity\Author as BaseAuthor;

/**
 * Entity Author with auto serialization for sampling purposes
 *
 * @package Samples\Domain\Entity\Auto
 * @author davamigo@gmail.com
 */
class Author extends BaseAuthor
{
    /**
     * @method create
     * @method serialize
     */
    use SerializableTrait;
}
