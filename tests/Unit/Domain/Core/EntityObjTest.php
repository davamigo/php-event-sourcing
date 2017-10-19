<?php

namespace Test\Unit\Domain\Core;

use Davamigo\Domain\Core\Entity\EntityBase;
use Davamigo\Domain\Core\Entity\EntityException;
use Davamigo\Domain\Core\Uuid\Uuid;
use Davamigo\Domain\Core\Uuid\UuidObj;
use PHPUnit\Framework\TestCase;

/**
 * Test of class Davamigo\Domain\Core\Entity\EntityObj
 *
 * @package Test\Unit\Domain\Core
 * @author davamigo@gmail.com
 *
 * @group Test_Unit_Domain_Core_EntityObj
 * @group Test_Unit_Domain_Core
 * @group Test_Unit_Domain
 * @group Test_Unit
 * @group Test
 * @test
 */
class EntityObjTest extends TestCase
{
    /**
     * Test empty constructor of EntityObj
     */
    public function testEmptyConstructor()
    {
        /** @var \Davamigo\Domain\Core\Serializable\EntityObj $entity */
        $entity = $this->createEntity(null);

        $uuid = $entity->uuid();
        $this->assertInstanceOf(Uuid::class, $uuid);
        $this->assertInternalType('string', $uuid->toString());
    }

    /**
     * Test constructor of EntityObj when an UUID provided
     */
    public function testConstructorWhenUuidProvided()
    {
        $uuid = UuidObj::create();

        /** @var \Davamigo\Domain\Core\Serializable\EntityObj $entity */
        $entity = $this->createEntity($uuid);

        $this->assertEquals($uuid, $entity->uuid());
    }

    /**
     * Test constructor of EntityObj with a string representing an UUID provided
     */
    public function testConstructorWhenStringProvided()
    {
        $rawUuid = '131cce48-b1c9-11e7-b650-15133b52a0df';

        /** @var \Davamigo\Domain\Core\Serializable\EntityObj $entity */
        $entity = $this->createEntity($rawUuid);

        $this->assertEquals($rawUuid, $entity->uuid()->toString());
    }

    /**
     * Test constructor of EntityObj when an invalid string provided
     */
    public function testConstructorWhenInvalidStringProvided()
    {
        $this->expectException(EntityException::class);

        $rawUuid = 'this_is_not_an_uuid_';

        $this->createEntity($rawUuid);
    }

    /**
     * Test constructor of EntityObj when an invalid object provided
     */
    public function testConstructorWhenInvalidObjectProvided()
    {
        $this->expectException(EntityException::class);

        $this->createEntity(new \DateTime());
    }

    /**
     * Creates an entity using the mock builder because EntityObj is an abstract class
     *
     * @param \Davamigo\Domain\Core\Uuid\Uuid|string|null $uuid
     * @return \Davamigo\Domain\Core\Entity\EntityBase|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createEntity($uuid)
    {
        return $this->getMockBuilder(\Davamigo\Domain\Core\Entity\EntityBase::class)
            ->setConstructorArgs([ $uuid ])
            ->setMethods(['create', 'serialize'])
            ->getMock();
    }
}
