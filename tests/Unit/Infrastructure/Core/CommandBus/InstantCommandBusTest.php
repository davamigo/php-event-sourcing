<?php

namespace Test\Unit\Infrastructure\Core\CommandBus;

use Davamigo\Domain\Core\Command\Command;
use Davamigo\Domain\Core\Command\CommandBase;
use Davamigo\Domain\Core\CommandBus\CommandBusException;
use Davamigo\Domain\Core\CommandHandler\CommandHandler;
use Davamigo\Domain\Core\CommandHandler\CommandHandlerException;
use Davamigo\Infrastructure\Core\CommandBus\InstantCommandBus;
use Davamigo\Domain\Core\Serializable\Serializable;
use Davamigo\Domain\Core\Serializable\SerializableTrait;
use Psr\Log\NullLogger;
use Test\Unit\Infrastructure\Core\AdvancedTestCase;

/**
 * Test of class Davamigo\Infrastructure\Core\CommandBus\InstantCommandBus
 *
 * @package Test\Unit\Infrastructure\Core\CommandBus
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
class InstantCommandBusTest extends AdvancedTestCase
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
            public function handledCommands()
            {
                return null;
            }
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
            public function handledCommands()
            {
                return null;
            }
            public function handle(Command $command): void
            {
            }
        };

        $commandHandler2 = new class implements CommandHandler {
            public function handledCommands()
            {
                return null;
            }
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
            public function handledCommands()
            {
                return 'command';
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
        };

        $logger = new NullLogger();

        $commandBus = new InstantCommandBus([ 'commandHandler' => $commandHandler], $logger);
        $commandBus->addCommand($command);
        $commandBus->dispatch();

        $this->assertEquals([], $this->getPrivateProperty($commandBus, 'commands'));
        $this->assertTrue($commandHandler->handleCalled);
    }

    /**
     * Test dispath function when no commands
     */
    public function testDispatchWhenNoCommands()
    {
        $logger = new NullLogger();

        $commandBus = new InstantCommandBus([], $logger);
        $commandBus->dispatch();

        $this->assertEquals([], $this->getPrivateProperty($commandBus, 'commands'));
    }

    /**
     * Test dispath function when no command handler
     */
    public function testDispatchWhenNoCommandHandler()
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
     * Test dispatch when the handler is not in the command
     */
    public function testDispatchWhenTheHandlerIsNotInTheCommand()
    {
        $commandHandler = new class implements CommandHandler {
            public $handleCalled = false;
            public function handle(Command $command): void
            {
                $this->handleCalled = true;
            }
            public function handledCommands()
            {
                return null;
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
        };

        $logger = new NullLogger();

        $commandBus = new InstantCommandBus([ 'command' => $commandHandler], $logger);
        $commandBus->addCommand($command);
        $commandBus->dispatch();

        $this->assertEquals([], $this->getPrivateProperty($commandBus, 'commands'));
        $this->assertTrue($commandHandler->handleCalled);
    }

    /**
     * Test dispatch when the handler is not in the command
     */
    public function testDispatchWhenManyHandlersPerCommand()
    {
        $commandHandler1 = new class implements CommandHandler {
            public $handleCalled = false;
            public function handle(Command $command): void
            {
                $this->handleCalled = true;
            }
            public function handledCommands()
            {
                return [
                    'command',
                    'command2'
                ];
            }
        };

        $commandHandler2 = new class implements CommandHandler {
            public $handleCalled = false;
            public function handle(Command $command): void
            {
                $this->handleCalled = true;
            }
            public function handledCommands()
            {
                return 'command';
            }
        };

        $commandHandler3 = new class implements CommandHandler {
            public $handleCalled = false;
            public function handle(Command $command): void
            {
                $this->handleCalled = true;
            }
            public function handledCommands()
            {
                return null;
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
        };

        $logger = new NullLogger();

        $commandHandlerCollection = [
            $commandHandler1,
            $commandHandler2,
            $commandHandler3
        ];

        $commandBus = new InstantCommandBus($commandHandlerCollection, $logger);
        $commandBus->addCommand($command);
        $commandBus->dispatch();

        $this->assertEquals([], $this->getPrivateProperty($commandBus, 'commands'));
        $this->assertTrue($commandHandler1->handleCalled);
        $this->assertTrue($commandHandler2->handleCalled);
        $this->assertFalse($commandHandler3->handleCalled);
    }

    /**
     * Test dispatch function when invalid command throws an exception
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
        };

        $commandHandler = new class implements CommandHandler {
            public function handledCommands()
            {
                return new \stdClass();
            }
            public function handle(Command $command): void
            {
            }
        };

        $this->expectException(CommandBusException::class);

        $logger = new NullLogger();

        $commandBus = new InstantCommandBus([ $commandHandler ], $logger);
        $commandBus->addCommand($command);
        $commandBus->dispatch();
    }

    /**
     * Test dispatch function when command handler throws an exception
     */
    public function testDispatchWhenCommandHandlerThrowsAnException()
    {
        $commandHandler = new class implements CommandHandler {
            public function handledCommands()
            {
                return 'command';
            }
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
        };

        $this->expectException(CommandBusException::class);

        $logger = new NullLogger();

        $commandBus = new InstantCommandBus([ 'commandHandler' => $commandHandler], $logger);
        $commandBus->addCommand($command);
        $commandBus->dispatch();
    }
}
