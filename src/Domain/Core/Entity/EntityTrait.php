<?php

namespace Davamigo\Domain\Core\Entity;

use Davamigo\Domain\Core\Uuid\Uuid;
use Davamigo\Domain\Core\Uuid\UuidException;
use Davamigo\Domain\Core\Uuid\UuidObj;

/**
 * Trait to auto implement the Entity interface
 *
 * @package Davamigo\Domain\Core\Entity
 * @author davamigo@gmail.com
 */
trait EntityTrait
{
    /** @var Uuid */
    private $uuid;

    /**
     * EntityObj constructor
     *
     * @param Uuid|string|null $uuid
     * @throws EntityException
     */
    public function __construct($uuid = null)
    {
        if (null === $uuid) {
            $this->uuid = UuidObj::create();
        } elseif (is_string($uuid)) {
            try {
                $this->uuid = UuidObj::fromString($uuid);
            } catch (UuidException $exc) {
                throw new EntityException('Error paring an UUID: ' . $uuid, 0, $exc);
            }
        } elseif (!$uuid instanceof Uuid) {
            throw new EntityException('Invalid UUID received: ' . get_class($uuid));
        } else {
            $this->uuid = $uuid;
        }
    }

    /**
     * Return the Uuid of the entity
     *
     * @return Uuid
     */
    public function uuid() : Uuid
    {
        return $this->uuid;
    }
}
