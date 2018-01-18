<?php

namespace Test\Unit\Domain\Core\CommandHandler;

use Davamigo\Domain\Core\Command\Command;
use Davamigo\Domain\Core\CommandHandler\CommandHandler;
use Davamigo\Domain\Core\CommandHandler\CommandHandlerCollection;
use Davamigo\Domain\Core\CommandHandler\CommandHandlerException;
use PHPUnit\Framework\TestCase;

/**
 * Test of class Davamigo\Domain\Core\CommandHandler\CommandHandlerCollection
 *
 * @package Test\Unit\Domain\Core\CommandHandler
 * @author davamigo@gmail.com
 *
 * @group Test_Unit_Domain_Core_CommandHandler_Collection
 * @group Test_Unit_Domain_Core_CommandHandler
 * @group Test_Unit_Domain_Core
 * @group Test_Unit_Domain
 * @group Test_Unit
 * @group Test
 * @test
 */
class CommandHandlerCollectionTest extends TestCase
{
    /**
     * CommandHandlerCollection::__construct()
     * CommandHandlerCollection::toArray()
     */
    public function testConstructorHappyPath()
    {
        $commandHandler1 = $this->createCommandHandlerStub();
        $commandHandler2 = $this->createCommandHandlerStub();

        $commandHandlers = [
            'commandHandler1' => $commandHandler1,
            'commandHandler2' => $commandHandler2
        ];

        $expected = $commandHandlers;

        $collection = new CommandHandlerCollection($commandHandlers);
        $result = $collection->toArray();

        $this->assertEquals($expected, $result);
    }

    /**
     * CommandHandlerCollection::__construct()
     * CommandHandlerCollection::toArray()
     */
    public function testConstructorWhenNoCommandHandlerNameProvided()
    {
        $commandHandler1 = $this->createCommandHandlerStub();
        $commandHandler2 = $this->createCommandHandlerStub();

        $commandHandlers = [
            $commandHandler1,
            $commandHandler2
        ];

        $expected = [
            get_class($commandHandler1) => $commandHandler1,
            get_class($commandHandler2) => $commandHandler2

        ];

        $collection = new CommandHandlerCollection($commandHandlers);
        $result = $collection->toArray();

        $this->assertEquals($expected, $result);
    }

    /**
     * CommandHandlerCollection::__construct()
     * CommandHandlerCollection::toArray()
     */
    public function testConstructorMixedCommandHandlerAndNoCommandHandlerNames()
    {
        $commandHandler1 = $this->createCommandHandlerStub();
        $commandHandler2 = $this->createCommandHandlerStub();

        $commandHandlers = [
            'commandHandler1' => $commandHandler1,
            $commandHandler2
        ];

        $expected = [
            'commandHandler1' => $commandHandler1,
            get_class($commandHandler2) => $commandHandler2

        ];

        $collection = new CommandHandlerCollection($commandHandlers);
        $result = $collection->toArray();

        $this->assertEquals($expected, $result);
    }

    /**
     * CommandHandlerCollection::__construct()
     * CommandHandlerCollection::toArray()
     */
    public function testConstructorWhenEmptyList()
    {
        $commandHandlers = [];

        $expected = $commandHandlers;

        $collection = new CommandHandlerCollection($commandHandlers);
        $result = $collection->toArray();

        $this->assertEquals($expected, $result);
    }

    /**
     * CommandHandlerCollection::__construct()
     * CommandHandlerCollection::toArray()
     */
    public function testConstructorWhenNoCommandHandlers()
    {
        $commandHandlers = [
            'commandHandler1' => new \DateTime()
        ];

        $this->expectException(CommandHandlerException::class);

        new CommandHandlerCollection($commandHandlers);
    }

    /**
     * CommandHandlerCollection::__construct()
     * CommandHandlerCollection::current()
     * CommandHandlerCollection::next()
     * CommandHandlerCollection::rewind()
     * CommandHandlerCollection::key()
     * CommandHandlerCollection::valid()
     */
    public function testCurrent()
    {
        $commandHandler1 = $this->createCommandHandlerStub();
        $commandHandler2 = $this->createCommandHandlerStub();

        $commandHandlers = [
            'commandHandler1' => $commandHandler1,
            'commandHandler2' => $commandHandler2
        ];

        $collection = new CommandHandlerCollection($commandHandlers);

        $this->assertEquals($commandHandler1, $collection->current());
        $this->assertEquals('commandHandler1', $collection->key());
        $this->assertTrue($collection->valid());

        $this->assertEquals($commandHandler2, $collection->next());
        $this->assertEquals('commandHandler2', $collection->key());
        $this->assertTrue($collection->valid());

        $this->assertFalse($collection->next());
        $this->assertNull($collection->key());
        $this->assertFalse($collection->valid());

        $this->assertEquals($commandHandler1, $collection->rewind());
        $this->assertEquals('commandHandler1', $collection->key());
        $this->assertTrue($collection->valid());
    }

    /**
     * Create commandHandler stub to use in the tests
     *
     * @param string[]|string|null $commands
     * @return CommandHandler
     */
    protected function createCommandHandlerStub($commands = null) : CommandHandler
    {
        return new class ($commands) implements CommandHandler {
            public $commands = null;
            public function __construct($commands)
            {
                $this->commands= $commands;
            }
            public function handledCommands()
            {
                return $this->commands;
            }
            public function handle(Command $command): void
            {
            }
        };
    }
}
