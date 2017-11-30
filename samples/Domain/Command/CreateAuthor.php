<?php

namespace Samples\Domain\Command;

use Davamigo\Domain\Core\Command\CommandBase;
use Davamigo\Domain\Core\Serializable\SerializableTrait;
use Davamigo\Domain\Core\Uuid\Uuid;
use Samples\Domain\Entity\Author as AuthorBase;
use Samples\Domain\Entity\Custom\Author as AuthorCustom;

/**
 * Command Create Author
 *
 * @package Samples\Domain\Command
 * @author davamigo@gmail.com
 */
class CreateAuthor extends CommandBase
{
    /**
     * CreateAuthor constructor.
     *
     * @param AuthorBase|null $author
     * @param Uuid|string|null $uuid
     * @param \DateTime        $createdAt
     * @param array            $metadata
     */
    public function __construct(
        AuthorBase $author = null,
        $uuid = null,
        \DateTime $createdAt = null,
        array $metadata = []
    ) {
        $name = self::class;
        $author = $author ?: new AuthorCustom();
        parent::__construct($name, $author, $uuid, $createdAt, $metadata);
    }

    /**
     * @method create
     * @method serialize
     */
    use SerializableTrait;
}
