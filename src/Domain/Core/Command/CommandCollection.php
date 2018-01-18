<?php

namespace Davamigo\Domain\Core\Command;

/**
 * Collection of commands
 *
 * @package Davamigo\Domain\Core\Command
 * @author davamigo@gmail.com
 */
class CommandCollection implements \Iterator
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
        $this->commands = [];
        foreach ($commands as $name => $command) {
            if (!$command instanceof Command) {
                throw new CommandException(
                    'CommandCollection: The items must be an instance of ' . Command::class
                );
            }
            if (is_numeric($name)) {
                $name = get_class($command);
            }
            $this->commands[$name] = $command;
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

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return current($this->commands);
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function next()
    {
        return next($this->commands);
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return key($this->commands);
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return key($this->commands) !== null;
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function rewind()
    {
        return reset($this->commands);
    }
}
