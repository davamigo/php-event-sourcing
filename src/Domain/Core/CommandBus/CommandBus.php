<?php

namespace Davamigo\Domain\Core\CommandBus;

use Davamigo\Domain\Core\Command\Command;
use Davamigo\Domain\Core\CommandHandler\CommandHandler;

/**
 * Interface for a command bus, which dispatches the commands to multiple handlers.
 *
 * @package Davamigo\Domain\Core\CommandBus
 * @author davamigo@gmail.com
 */
interface CommandBus
{
    /**
     * Adds a handler to the command bus.
     *
     * @param string         $name    The name of the handler. Usually the full name of the class.
     * @param CommandHandler $handler The handler of the command with it's dependencies solved.
     * @return $this
     * @throws CommandBusException
     */
    public function addHandler(string $name, CommandHandler $handler);

    /**
     * Adds a command to the command bus. The command know its handlers.
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
