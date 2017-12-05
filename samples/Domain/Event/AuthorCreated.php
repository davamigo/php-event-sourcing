<?php

namespace Samples\Domain\Event;

use Davamigo\Domain\Core\Event\EventBase;
use Davamigo\Domain\Core\Serializable\SerializableTrait;
use Davamigo\Domain\Core\Uuid\Uuid;
use Samples\Domain\Entity\Author as AuthorBase;
use Samples\Domain\Entity\Custom\Author as AuthorCustom;

/**
 * Event Author Created
 *
 * @package Samples\Domain\Event
 * @author davamigo@gmail.com
 */
class AuthorCreated extends EventBase
{
    /**
     * AuthorCreated constructor.
     *
     * @param AuthorBase|null $author
     * @param Uuid|string|null $uuid
     * @param \DateTime        $createdAt
     * @param array            $metadata
     */
    public function __construct(
        AuthorBase $author = null,
        $topic = null,
        $routingKey = null,
        $uuid = null,
        \DateTime $createdAt = null,
        array $metadata = []
    ) {
        $name = self::class;
        $author = $author ?: new AuthorCustom();
        parent::__construct($name, $author, $topic, $routingKey, $uuid, $createdAt, $metadata);
    }

    /**
     * @method create
     * @method serialize
     */
    use SerializableTrait;
}
