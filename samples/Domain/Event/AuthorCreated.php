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
     * @param AuthorBase|null  $author
     * @param array            $metadata
     * @param \DateTime|null   $createdAt
     * @param Uuid|string|null $uuid
     */
    public function __construct(
        AuthorBase $author = null,
        array $metadata = [],
        \DateTime $createdAt = null,
        $uuid = null
    ) {
        $name = self::class;
        $author = $author ?: new AuthorCustom();
        parent::__construct($name, self::ACTION_INSERT, $author, $metadata, $createdAt, $uuid);
    }

    /**
     * @method create
     * @method serialize
     */
    use SerializableTrait;
}
