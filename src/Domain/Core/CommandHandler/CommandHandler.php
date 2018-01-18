<?php

namespace Davamigo\Domain\Core\CommandHandler;

use Davamigo\Domain\Core\Command\Command;

/**
 * Interface for a command handler
 *
 * @package Davamigo\Domain\Core\CommandHandler
 * @author davamigo@gmail.com
 */
interface CommandHandler
{
    /**
     * Return the names of the commands who handle this command handler
     *
     * @return string[]|string|null
     */
    public function handledCommands();

    /**
     * Handles a command.
     *
     * @param Command $command
     * @return void
     * @throws CommandHandlerException
     */
    public function handle(Command $command) : void;
}
