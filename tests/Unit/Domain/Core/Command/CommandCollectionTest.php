<?php

namespace Test\Unit\Domain\Core\Command;

use Davamigo\Domain\Core\Command\Command;
use Davamigo\Domain\Core\Command\CommandBase;
use Davamigo\Domain\Core\Command\CommandCollection;
use Davamigo\Domain\Core\Command\CommandException;
use Davamigo\Domain\Core\Entity\EntityBase;
use Davamigo\Domain\Core\Serializable\SerializableTrait;
use PHPUnit\Framework\TestCase;

/**
 * Test of class Davamigo\Domain\Core\Command\CommandCollection
 *
 * @package Test\Unit\Domain\Core\Command
 * @author davamigo@gmail.com
 *
 * @group Test_Unit_Domain_Core_Command_Collection
 * @group Test_Unit_Domain_Core_Command
 * @group Test_Unit_Domain_Core
 * @group Test_Unit_Domain
 * @group Test_Unit
 * @group Test
 * @test
 */
class CommandCollectionTest extends TestCase
{
    /**
     * CommandCollection::__construct()
     * CommandCollection::toArray()
     */
    public function testConstructorHappyPath()
    {
        $command1 = $this->createCommandStub('command1');
        $command2 = $this->createCommandStub('command2');

        $commands = [
            'command1' => $command1,
            'command2' => $command2
        ];

        $expected = $commands;

        $collection = new CommandCollection($commands);
        $result = $collection->toArray();

        $this->assertEquals($expected, $result);
    }

    /**
     * CommandCollection::__construct()
     * CommandCollection::toArray()
     */
    public function testConstructorWhenNoCommandNameProvided()
    {
        $command1 = $this->createCommandStub('command1');
        $command2 = $this->createCommandStub('command2');

        $commands = [
            $command1,
            $command2
        ];

        $expected = [
            get_class($command1) => $command1,
            get_class($command2) => $command2

        ];

        $collection = new CommandCollection($commands);
        $result = $collection->toArray();

        $this->assertEquals($expected, $result);
    }

    /**
     * CommandCollection::__construct()
     * CommandCollection::toArray()
     */
    public function testConstructorMixedCommandAndNoCommandNames()
    {
        $command1 = $this->createCommandStub('command1');
        $command2 = $this->createCommandStub('command2');

        $commands = [
            'command1' => $command1,
            $command2
        ];

        $expected = [
            'command1' => $command1,
            get_class($command2) => $command2

        ];

        $collection = new CommandCollection($commands);
        $result = $collection->toArray();

        $this->assertEquals($expected, $result);
    }

    /**
     * CommandCollection::__construct()
     * CommandCollection::toArray()
     */
    public function testConstructorWhenEmptyList()
    {
        $commands = [];

        $expected = $commands;

        $collection = new CommandCollection($commands);
        $result = $collection->toArray();

        $this->assertEquals($expected, $result);
    }

    /**
     * CommandCollection::__construct()
     * CommandCollection::toArray()
     */
    public function testConstructorWhenNoCommands()
    {
        $commands = [
            'command1' => new \DateTime()
        ];

        $this->expectException(CommandException::class);

        new CommandCollection($commands);
    }

    /**
     * CommandCollection::__construct()
     * CommandCollection::current()
     * CommandCollection::next()
     * CommandCollection::rewind()
     * CommandCollection::key()
     * CommandCollection::valid()
     */
    public function testCurrent()
    {
        $command1 = $this->createCommandStub('command1');
        $command2 = $this->createCommandStub('command2');

        $commands = [
            'command1' => $command1,
            'command2' => $command2
        ];

        $collection = new CommandCollection($commands);

        $this->assertEquals($command1, $collection->current());
        $this->assertEquals('command1', $collection->key());
        $this->assertTrue($collection->valid());

        $this->assertEquals($command2, $collection->next());
        $this->assertEquals('command2', $collection->key());
        $this->assertTrue($collection->valid());

        $this->assertFalse($collection->next());
        $this->assertNull($collection->key());
        $this->assertFalse($collection->valid());

        $this->assertEquals($command1, $collection->rewind());
        $this->assertEquals('command1', $collection->key());
        $this->assertTrue($collection->valid());
    }

    /**
     * Create command stub to use in the tests
     *
     * @param string $commandName
     * @return Command
     */
    protected function createCommandStub(string $commandName) : Command
    {
        return new class ($commandName) extends CommandBase {
            use SerializableTrait;
            public function __construct(string $commandName)
            {
                parent::__construct($commandName, new class extends EntityBase {
                    use SerializableTrait;
                });
            }
        };
    }
}
