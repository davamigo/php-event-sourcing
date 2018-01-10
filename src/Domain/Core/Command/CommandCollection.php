<?php

namespace Davamigo\Domain\Core\Command;

/**
 * Collection of commands
 *
 * @package Davamigo\Domain\Core\Command
 * @author davamigo@gmail.com
 */
class CommandCollection
{
    /** @var Command[] */
    protected $commands;

    /**
     * CommandCollection constructor.
     *
     * @param iterable $commands
     * @throws CommandException
     */
    public function __construct(iterable $commands)
    {
        foreach ($commands as $command) {
            if (!$command instanceof Command) {
                throw new CommandException('The items must be an instance of ' . Command::class);
            }
            $this->commands[] = $command;
        }
    }

    /**
     * Gests the commands collection as an array
     *
     * @return array
     */
    public function toArray() : array
    {
        return $this->commands;
    }
}
