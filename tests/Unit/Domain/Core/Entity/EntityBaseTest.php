<?php

namespace Test\Unit\Domain\Core\Entity;

use Davamigo\Domain\Core\Entity\EntityBase;
use Davamigo\Domain\Core\Entity\EntityException;
use Davamigo\Domain\Core\Uuid\Uuid;
use Davamigo\Domain\Core\Uuid\UuidObj;
use PHPUnit\Framework\TestCase;

/**
 * Test of class Davamigo\Domain\Core\Entity\EntityBase
 *
 * @package Test\Unit\Domain\Core\Entity
 * @author davamigo@gmail.com
 *
 * @group Test_Unit_Domain_Core_Entity_Base
 * @group Test_Unit_Domain_Core_Entity
 * @group Test_Unit_Domain_Core
 * @group Test_Unit_Domain
 * @group Test_Unit
 * @group Test
 * @test
 */
class EntityBaseTest extends TestCase
{
    /**
     * Test empty constructor of EntityBase
     */
    public function testEmptyConstructor()
    {
        /** @var EntityBase $entity */
        $entity = $this->createEntity(null);

        $uuid = $entity->uuid();
        $this->assertInstanceOf(Uuid::class, $uuid);
        $this->assertInternalType('string', $uuid->toString());
    }

    /**
     * Test constructor of EntityBase when an UUID provided
     */
    public function testConstructorWhenUuidProvided()
    {
        $uuid = UuidObj::create();

        /** @var EntityBase $entity */
        $entity = $this->createEntity($uuid);

        $this->assertEquals($uuid, $entity->uuid());
    }

    /**
     * Test constructor of EntityBase with a string representing an UUID provided
     */
    public function testConstructorWhenStringProvided()
    {
        $rawUuid = '131cce48-b1c9-11e7-b650-15133b52a0df';

        /** @var EntityBase $entity */
        $entity = $this->createEntity($rawUuid);

        $this->assertEquals($rawUuid, $entity->uuid()->toString());
    }

    /**
     * Test constructor of EntityBase when an invalid string provided
     */
    public function testConstructorWhenInvalidStringProvided()
    {
        $this->expectException(EntityException::class);

        $rawUuid = 'this_is_not_an_uuid_';

        $this->createEntity($rawUuid);
    }

    /**
     * Test constructor of EntityBase when an invalid object provided
     */
    public function testConstructorWhenInvalidObjectProvided()
    {
        $this->expectException(EntityException::class);

        $this->createEntity(new \DateTime());
    }

    /**
     * Creates an entity using the mock builder because EntityBase is an abstract class
     *
     * @param \Davamigo\Domain\Core\Uuid\Uuid|string|null $uuid
     * @return \Davamigo\Domain\Core\Entity\EntityBase|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createEntity($uuid)
    {
        return $this->getMockBuilder(EntityBase::class)
            ->setConstructorArgs([ $uuid ])
            ->setMethods(['create', 'serialize'])
            ->getMock();
    }
}
