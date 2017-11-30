<?php

namespace Test\Unit\Domain\Core;

use Davamigo\Domain\Core\Entity\EntityBase;
use Davamigo\Domain\Core\Command\CommandBase;
use Davamigo\Domain\Core\Command\CommandException;
use Davamigo\Domain\Core\Serializable\Serializable;
use Davamigo\Domain\Core\Serializable\SerializableTrait;
use Davamigo\Domain\Core\Uuid\Uuid;
use PHPUnit\Framework\TestCase;

/**
 * Test of class Davamigo\Domain\Core\Command\CommandBase
 *
 * @package Test\Unit\Domain\Core
 * @author davamigo@gmail.com
 *
 * @group Test_Unit_Domain_Core_CommandBase
 * @group Test_Unit_Domain_Core
 * @group Test_Unit_Domain
 * @group Test_Unit
 * @group Test
 * @test
 */
class CommandBaseTest extends TestCase
{
    /**
     * Test minimal constructor of CommandBase class
     */
    public function testMinimalConstructor()
    {
        $entity = new class extends EntityBase {
            use SerializableTrait;
        };

        $command = $this->createCommand('command_name', $entity);

        $this->assertEquals('command', $command->type());
        $this->assertEquals('command_name', $command->name());
        $this->assertEquals($entity, $command->payload());
        $this->assertInstanceOf(Uuid::class, $command->uuid());
        $this->assertInstanceOf(\DateTime::class, $command->createdAt());
        $this->assertInternalType('array', $command->metadata());
    }

    /**
     * Test full constructor of CommandBase class
     */
    public function testFullConstructor()
    {
        $serializable = new class implements Serializable {
            use SerializableTrait;
        };

        $command = $this->createCommand(
            'the_name',
            $serializable,
            'baf44167-95f1-44d3-b9fd-645b5f05dd9d',
            \DateTime::createFromFormat('d-m-Y', '20-02-2002'),
            [ 'a', 'b', 'c' ]
        );

        $this->assertEquals('the_name', $command->name());
        $this->assertEquals($serializable, $command->payload());
        $this->assertEquals('baf44167-95f1-44d3-b9fd-645b5f05dd9d', $command->uuid()->toString());
        $this->assertEquals('20-02-2002', $command->createdAt()->format('d-m-Y'));
        $this->assertEquals([ 'a', 'b', 'c' ], $command->metadata());
    }

    /**
     * Test constructor of CommandBase class throws an exception when no name
     */
    public function testConstructorWithoutTypeThrowsAnException()
    {
        $this->expectException(CommandException::class);

        $serializable = new class implements Serializable {
            use SerializableTrait;
        };

        $this->createCommand('', $serializable);
    }

    /**
     * Returns a new CommandBase object
     *
     * @param $name
     * @param $payload
     * @param $uuid
     * @param $createdAt
     * @param $metadata
     * @return CommandBase
     */
    private function createCommand($name = null, $payload = null, $uuid = null, $createdAt = null, $metadata = [])
    {
        return new class($name, $payload, $uuid, $createdAt, $metadata) extends CommandBase {
            use SerializableTrait;
        };
    }
}
