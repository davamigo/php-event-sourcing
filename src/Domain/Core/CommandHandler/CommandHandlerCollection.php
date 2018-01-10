<?php

namespace Davamigo\Domain\Core\CommandHandler;

/**
 * Collection of command handlers
 *
 * @package Davamigo\Domain\Core\CommandHandler
 * @author davamigo@gmail.com
 */
class CommandHandlerCollection implements \Iterator
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
        $this->handlers = [];
        foreach ($handlers as $name => $handler) {
            if (!$handler instanceof CommandHandler) {
                throw new CommandHandlerException('The items must be an instance of ' . CommandHandler::class);
            }
            if (is_numeric($name)) {
                $name = get_class($handler);
            }
            $this->handlers[$name] = $handler;
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

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return current($this->handlers);
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        next($this->handlers);
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return key($this->handlers);
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
        return key($this->handlers) !== null;
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        reset($this->handlers);
    }
}
