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
     * @param array            $metadata
     * @param \DateTime        $createdAt
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
        parent::__construct($name, $author, $metadata, $createdAt, $uuid);
    }

    /**
     * @method create
     * @method serialize
     */
    use SerializableTrait;
}
