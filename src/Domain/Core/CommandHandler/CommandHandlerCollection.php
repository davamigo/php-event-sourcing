<?php

namespace Davamigo\Domain\Core\CommandHandler;

/**
 * Collection of command handlers
 *
 * @package Davamigo\Domain\Core\CommandHandler
 * @author davamigo@gmail.com
 */
class CommandHandlerCollection
{
    /** @var CommandHandler[] */
    protected $handlers;

    /**
     * CommandHandlerCollection constructor.
     *
     * @param iterable $handlers
     * @throws CommandHandlerException
     */
    public function __construct(iterable $handlers)
    {
        foreach ($handlers as $handler) {
            if (!$handler instanceof CommandHandler) {
                throw new CommandHandlerException('The items must be an instance of ' . CommandHandler::class);
            }
            $this->handlers[] = $handler;
        }
    }

    /**
     * Gests the command handler collection as an array
     *
     * @return array
     */
    public function toArray() : array
    {
        return $this->handlers;
    }
}
