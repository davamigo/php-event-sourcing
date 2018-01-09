<?php

namespace Davamigo\Domain\Core\EntityProjector;
use Davamigo\Domain\Core\Entity\Entity;
use Davamigo\Domain\Core\Uuid\Uuid;

/**
 * Interface for an entity projector who projects the entity reading from the event storage
 *
 * @package Davamigo\Domain\Core\EntityProjector
 * @author davamigo@gmail.com
 */
interface EntityProjector
{
    /**
     * Finds an entity reading from the event storage
     *
     * @param Uuid  $uuid
     * @param array $options
     * @return Entity
     */
    public function findEntity(Uuid $uuid, array $options = []) : Entity;
}
