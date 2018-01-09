<?php

namespace Davamigo\Domain\Core\Command;

use Davamigo\Domain\Core\Message\Message;
use Davamigo\Domain\Core\Message\MessageBase;
use Davamigo\Domain\Core\Message\MessageException;
use Davamigo\Domain\Core\Serializable\Serializable;
use Davamigo\Domain\Core\Uuid\Uuid;

/**
 *  Abstract class for an command object. An command is a message with some payload.
 *
 * @package Davamigo\Domain\Core\Command
 * @author davamigo@gmail.com
 */
abstract class CommandBase extends MessageBase implements Command
{
    /** @var Serializable */
    private $payload;

    /**
     * CommandBase constructor.
     *
     * @param string           $name
     * @param Serializable     $payload,
     * @param array            $metadata
     * @param \DateTime        $createdAt
     * @param Uuid|string|null $uuid
     */
    public function __construct(
        string $name,
        Serializable $payload,
        array $metadata = [],
        \DateTime $createdAt = null,
        $uuid = null
    ) {
        try {
            parent::__construct(Message::TYPE_COMMAND, $name, $metadata, $createdAt, $uuid);
        } catch (MessageException $exc) {
            throw new CommandException($exc->getMessage(), 0, $exc);
        }

        $this->payload = $payload;
    }

    /**
     * Return the payload of the command
     *
     * @return Serializable
     */
    public function payload()
    {
        return $this->payload;
    }

    /**
     * Return the command handler class names for this command. For example: 'Samples\Domain\Command\CreateAutor' or
     * [ CreateAuthorInsert::class, CreateAuthorSendMail::class ]
     *
     * @return string[]|string|null
     */
    public function commandHandlers()
    {
        return null;
    }
}
