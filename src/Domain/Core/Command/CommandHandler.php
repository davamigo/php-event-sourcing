<?php

namespace Davamigo\Domain\Core\Command;

/**
 * Interface for a command handler
 *
 * @package Davamigo\Domain\Core\Command
 * @author davamigo@gmail.com
 */
interface CommandHandler
{
    /**
     * Handles a command
     *
     * @param Command $command
     * @return void
     */
    public function handle(Command $command) : void;
}
