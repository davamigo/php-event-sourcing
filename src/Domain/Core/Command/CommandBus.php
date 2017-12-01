<?php

namespace Davamigo\Domain\Core\Command;

/**
 * Interface for a command bus, which dispatches the commands.
 *
 * @package Davamigo\Domain\Core\Command
 * @author davamigo@gmail.com
 */
interface CommandBus
{
    /**
     * Adds a command to the command bus.
     *
     * @param Command $command
     * @return $this
     * @throws CommandBusException
     */
    public function addCommand(Command $command) : CommandBus;

    /**
     * Dispatch al the commands of the command bus.
     *
     * @return $this
     * @throws CommandBusException
     */
    public function dispatch() : CommandBus;
}
