<?php

namespace Davamigo\Domain\Core\Command;

use Psr\Log\LoggerInterface;

/**
 * Simple instant command bus.
 *
 * @package Davamigo\Domain\Core\Command
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
     * @param array $handlers
     * @param LoggerInterface $logger
     */
    public function __construct(array $handlers = [], LoggerInterface $logger = null)
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
     * @param string         $name
     * @param CommandHandler $handler
     * @return $this
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
                $this->notice('Notice: Command ' . $command->name() . ' has no handlers');
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
        $commandHandlerNames = $command->commandHandlers();
        if (null === $commandHandlerNames) {
            $commandHandlerNames = [];
        } elseif (is_string($commandHandlerNames)) {
            $commandHandlerNames = [ $commandHandlerNames ];
        } elseif (!is_array($commandHandlerNames)) {
            $msg = 'Command ' . $command->name() . ' has a invalid handler!';
            $this->error($msg);
            throw new CommandBusException($msg);
        }

        $commandHandlers = [];
        foreach ($commandHandlerNames as $handlerName) {
            if (!array_key_exists($handlerName, $this->handlers)) {
                $msg = 'Handler ' . $handlerName . ' for command ' . $command->name() . ' not found!';
                $this->error($msg);
                throw new CommandBusException($msg);
            } else {
                $commandHandlers[] = $this->handlers[$handlerName];
            }
        }

        return $commandHandlers;
    }

    /**
     * Log a normal but significant event.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    protected function notice($message, array $context = array())
    {
        if (null !== $this->logger) {
            $this->logger->notice($message, $context);
        }
    }

    /**
     * Log a interesting event.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    protected function info($message, array $context = array())
    {
        if (null !== $this->logger) {
            $this->logger->info($message, $context);
        }
    }

    /**
     * Log an error.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    protected function error($message, array $context = array())
    {
        if (null !== $this->logger) {
            $this->logger->error($message, $context);
        }
    }
}
