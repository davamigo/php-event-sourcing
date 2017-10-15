<?php

namespace Test\Unit\Domain\Core;

use Davamigo\Domain\Core\EntityObj;
use Davamigo\Domain\Core\Uuid;
use Davamigo\Domain\Core\UuidObj;
use PHPUnit\Framework\TestCase;

/**
 * Test of class Davamigo\Domain\Core\EntityObj
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
        /** @var EntityObj $entity */
        $entity = $this->getMockBuilder(EntityObj::class)
            ->setMethods(['create', 'serialize'])
            ->getMock();

        $uuid = $entity->uuid();
        $this->assertInstanceOf(Uuid::class, $uuid);
        $this->assertInternalType('string', $uuid->toString());
    }

    /**
     * Test non empty constructor od EntityObj
     */
    public function testNonEmptyConstructor()
    {
        $rawUuid = '131cce48-b1c9-11e7-b650-15133b52a0df';

        /** @var EntityObj $entity */
        $entity = $this->getMockBuilder(EntityObj::class)
            ->setConstructorArgs([ UuidObj::fromString($rawUuid) ])
            ->setMethods(['create', 'serialize'])
            ->getMock();

        $this->assertEquals($rawUuid, $entity->uuid()->toString());
    }
}
