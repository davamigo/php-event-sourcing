<?php

namespace Davamigo\Infrastructure\Core\CommandBus;

use Davamigo\Domain\Core\Command\Command;
use Davamigo\Domain\Core\CommandBus\CommandBus;
use Davamigo\Domain\Core\CommandBus\CommandBusException;
use Davamigo\Domain\Core\CommandHandler\CommandHandler;
use Davamigo\Domain\Core\CommandHandler\CommandHandlerException;
use Psr\Log\LoggerInterface;

/**
 * Simple instant command bus.
 *
 * @package Davamigo\Infrastructure\Core\CommandBus
 * @author davamigo@gmail.com
 */
class InstantCommandBus implements CommandBus
{
    /** @var CommandHandler[] */
    protected $handlers = [];

    /** @var Command[] */
    protected $commands = [];

    /** @var LoggerInterface */
    protected $logger = null;

    /**
     * InstantCommandBus constructor.
     *
     * @param iterable $handlers
     * @param LoggerInterface $logger
     */
    public function __construct(iterable $handlers = [], LoggerInterface $logger = null)
    {
        $this->handlers = [];
        $this->commands = [];
        $this->logger = $logger;
        foreach ($handlers as $name => $handler) {
            $this->addHandler($name, $handler);
        }
    }

    /**
     * Adds a handler to the command bus.
     *
     * @param string         $name    The name of the handler. Usually the full name of the class.
     * @param CommandHandler $handler The handler of the command with it's dependencies solved.
     * @return $this
     * @throws CommandBusException
     */
    public function addHandler(string $name, CommandHandler $handler)
    {
        $this->handlers[$name] = $handler;
        return $this;
    }

    /**
     * Adds a command to the command bus.
     *
     * @param Command $command
     * @return $this
     * @throws CommandBusException
     */
    public function addCommand(Command $command): CommandBus
    {
        $this->commands[] = $command;
        return $this;
    }

    /**
     * Dispatch al the commands of the command bus.
     *
     * @return $this
     * @throws CommandBusException
     */
    public function dispatch(): CommandBus
    {
        while (null !== ($command = array_shift($this->commands))) {
            $this->info('Dispatching command:' . $command->name());

            $commandHandlers = $this->getCommandHandlers($command);
            if (empty($commandHandlers)) {
                $this->warning('Warning: Command ' . $command->name() . ' has no handlers');
            }

            foreach ($commandHandlers as $commandHandler) {
                $this->info('Executing command handler: ' . get_class($commandHandler));
                try {
                    $commandHandler->handle($command);
                } catch (CommandHandlerException $exc) {
                    $msg = 'An error occurred executing command handler ' . get_class($commandHandler);
                    $this->error($msg);
                    throw new CommandBusException($msg, 0, $exc);
                }
            }
        }

        return $this;
    }

    /**
     * Gets the command handler names from the command and finds the real command handlers.
     *
     * @param Command $command
     * @return CommandHandler[]
     * @throws CommandBusException
     */
    private function getCommandHandlers(Command $command) : array
    {
        $commandHandlers = [];
        foreach ($this->handlers as $handler) {
            $commandNames = $handler->handledCommands();
            if (null === $commandNames) {
                $commandNames = [];
            } elseif (is_string($commandNames)) {
                $commandNames = [ $commandNames ];
            } elseif (!is_array($commandNames)) {
                $msg = 'Command ' . $command->name() . ' has a invalid handlers!';
                $this->error($msg);
                throw new CommandBusException($msg);
            }

            foreach ($commandNames as $commandName) {
                if ($commandName == $command->name()) {
                    $commandHandlers[] = $handler;
                }
            }
        }

        if (empty($commandHandlers) && array_key_exists($command->name(), $this->handlers)) {
            $commandHandlers[] = $this->handlers[$command->name()];
        }

        return $commandHandlers;
    }

    /**
     * Log an informational event.
     *
     * @param string $message
     * @param array  $context
     * @return void
     */
    protected function info($message, array $context = array())
    {
        if (null !== $this->logger) {
            $this->logger->info($message, $context);
        }
    }

    /**
     * Log a warning event.
     *
     * @param string $message
     * @param array  $context
     * @return void
     */
    protected function warning($message, array $context = array())
    {
        if (null !== $this->logger) {
            $this->logger->warning($message, $context);
        }
    }

    /**
     * Log an error event.
     *
     * @param string $message
     * @param array  $context
     * @return void
     */
    protected function error($message, array $context = array())
    {
        if (null !== $this->logger) {
            $this->logger->error($message, $context);
        }
    }
}
