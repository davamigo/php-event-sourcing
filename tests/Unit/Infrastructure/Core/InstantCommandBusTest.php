<?php

namespace Test\Unit\Infrastructure\Core;

use Davamigo\Domain\Core\Command\Command;
use Davamigo\Domain\Core\Command\CommandBase;
use Davamigo\Domain\Core\Command\CommandBusException;
use Davamigo\Domain\Core\Command\CommandHandler;
use Davamigo\Domain\Core\Command\CommandHandlerException;
use Davamigo\Infrastructure\Core\Command\InstantCommandBus;
use Davamigo\Domain\Core\Serializable\Serializable;
use Davamigo\Domain\Core\Serializable\SerializableTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Test of class Davamigo\Infrastructure\Core\Command\InstantCommandBus
 *
 * @package Test\Unit\Infrastructure\Core
 * @author davamigo@gmail.com
 *
 * @group Test_Unit_Infrastructure_Core_CommandBus_Instant
 * @group Test_Unit_Infrastructure_Core_CommandBus
 * @group Test_Unit_Infrastructure_Core
 * @group Test_Unit_Infrastructure
 * @group Test_Unit
 * @group Test
 * @test
 */
class InstantCommandBusTest extends TestCase
{
    /**
     * Test empty constructor
     */
    public function testEmptyConstructor()
    {
        $commandBus = new InstantCommandBus();

        $this->assertEquals([], $this->getPrivateProperty($commandBus, 'handlers'));
        $this->assertEquals([], $this->getPrivateProperty($commandBus, 'commands'));
        $this->assertNull($this->getPrivateProperty($commandBus, 'logger'));
    }

    /**
     * Test regular constructor
     */
    public function testRegularConstructor()
    {
        $commandHandler = new class implements CommandHandler {
            public function handle(Command $command): void
            {
            }
        };

        $commandHandlers = [
            'commandHandler' => $commandHandler
        ];

        $logger = new NullLogger();

        $commandBus = new InstantCommandBus($commandHandlers, $logger);

        $this->assertEquals($commandHandlers, $this->getPrivateProperty($commandBus, 'handlers'));
        $this->assertEquals([], $this->getPrivateProperty($commandBus, 'commands'));
        $this->assertEquals($logger, $this->getPrivateProperty($commandBus, 'logger'));
    }

    /**
     * Test AddHandler function
     */
    public function testAddHandler()
    {
        $commandHandler1 = new class implements CommandHandler {
            public function handle(Command $command): void
            {
            }
        };

        $commandHandler2 = new class implements CommandHandler {
            public function handle(Command $command): void
            {
            }
        };

        $commandBus = new InstantCommandBus();
        $commandBus->addHandler('commandHandler1', $commandHandler1);
        $commandBus->addHandler('commandHandler2', $commandHandler2);

        $expected = [
            'commandHandler1' => $commandHandler1,
            'commandHandler2' => $commandHandler2,
        ];

        $this->assertEquals($expected, $this->getPrivateProperty($commandBus, 'handlers'));
    }

    /**
     * Test AddCommand function
     */
    public function testAddCommand()
    {
        $command1 = new class extends CommandBase {
            use SerializableTrait;
            public function __construct()
            {
                parent::__construct('command1', new class implements Serializable {
                    use SerializableTrait;
                });
            }
        };

        $command2 = new class extends CommandBase {
            use SerializableTrait;
            public function __construct()
            {
                parent::__construct('command2', new class implements Serializable {
                    use SerializableTrait;
                });
            }
        };

        $commandBus = new InstantCommandBus();
        $commandBus->addCommand($command1);
        $commandBus->addCommand($command2);

        $expected = [
            $command1,
            $command2,
        ];

        $this->assertEquals($expected, $this->getPrivateProperty($commandBus, 'commands'));
    }

    /**
     * Test dispath function when all works
     */
    public function testDispatchHappyPath()
    {
        $commandHandler = new class implements CommandHandler {
            public $handleCalled = false;
            public function handle(Command $command): void
            {
                $this->handleCalled = true;
            }
        };

        $command = new class extends CommandBase {
            use SerializableTrait;
            public function __construct()
            {
                parent::__construct('command', new class implements Serializable {
                    use SerializableTrait;
                });
            }
            public function commandHandlers()
            {
                return 'commandHandler';
            }
        };

        $logger = new NullLogger();

        $commandBus = new InstantCommandBus([ 'commandHandler' => $commandHandler], $logger);
        $commandBus->addCommand($command);
        $commandBus->dispatch();

        $this->assertEquals([], $this->getPrivateProperty($commandBus, 'commands'));
        $this->assertTrue($commandHandler->handleCalled);
    }

    /**
     * Test dispath function when no commands does nothing
     */
    public function testDispatchWhenNoCommandsDoesNothing()
    {
        $logger = new NullLogger();

        $commandBus = new InstantCommandBus([], $logger);
        $commandBus->dispatch();

        $this->assertEquals([], $this->getPrivateProperty($commandBus, 'commands'));
    }

    /**
     * Test dispath function when no command handler does nothing
     */
    public function testDispatchWhenNoCommandHandlerDoesNothing()
    {
        $command = new class extends CommandBase {
            use SerializableTrait;
            public function __construct()
            {
                parent::__construct('command', new class implements Serializable {
                    use SerializableTrait;
                });
            }
        };

        $logger = new NullLogger();

        $commandBus = new InstantCommandBus([], $logger);
        $commandBus->addCommand($command);
        $commandBus->dispatch();

        $this->assertEquals([], $this->getPrivateProperty($commandBus, 'commands'));
    }

    /**
     * Test dispath function when invalid command throws an exception
     */
    public function testDispatchWhenInvalidCommandHandlerThrowsAnException()
    {
        $command = new class extends CommandBase {
            use SerializableTrait;
            public function __construct()
            {
                parent::__construct('command', new class implements Serializable {
                    use SerializableTrait;
                });
            }
            public function commandHandlers()
            {
                return new \stdClass();
            }
        };

        $this->expectException(CommandBusException::class);

        $logger = new NullLogger();

        $commandBus = new InstantCommandBus([], $logger);
        $commandBus->addCommand($command);
        $commandBus->dispatch();
    }

    /**
     * Test dispath function when command not found throws an exception
     */
    public function testDispatchWhenCommandHandlerNotFoundThrowsAnException()
    {
        $command = new class extends CommandBase {
            use SerializableTrait;
            public function __construct()
            {
                parent::__construct('command', new class implements Serializable {
                    use SerializableTrait;
                });
            }
            public function commandHandlers()
            {
                return 'commandHandler';
            }
        };

        $this->expectException(CommandBusException::class);

        $logger = new NullLogger();

        $commandBus = new InstantCommandBus([], $logger);
        $commandBus->addCommand($command);
        $commandBus->dispatch();
    }

    /**
     * Test dispath function when command handler throws an exception
     */
    public function testDispatchWhenCommandHandlerThrowsAnException()
    {
        $commandHandler = new class implements CommandHandler {
            public function handle(Command $command): void
            {
                throw new CommandHandlerException();
            }
        };

        $command = new class extends CommandBase {
            use SerializableTrait;
            public function __construct()
            {
                parent::__construct('command', new class implements Serializable {
                    use SerializableTrait;
                });
            }
            public function commandHandlers()
            {
                return 'commandHandler';
            }
        };

        $this->expectException(CommandBusException::class);

        $logger = new NullLogger();

        $commandBus = new InstantCommandBus([ 'commandHandler' => $commandHandler], $logger);
        $commandBus->addCommand($command);
        $commandBus->dispatch();
    }

    /**
     * Get the value of a private property of an object using reflection
     *
     * @param object $object
     * @param string $property
     * @return mixed
     */
    private function getPrivateProperty($object, string $property)
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($property);
        $reflectionProperty->setAccessible(true);
        return $reflectionProperty->getValue($object);
    }
}
