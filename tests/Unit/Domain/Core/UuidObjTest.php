<?php

namespace Test\Unit\Domain\Core;

use Davamigo\Domain\Core\Uuid\UuidException;
use Davamigo\Domain\Core\Uuid\UuidObj;
use PHPUnit\Framework\TestCase;

/**
 * Test of class Davamigo\Domain\Core\Uuid\UuidObj
 *
 * @package Test\Unit\Domain\Core
 * @author davamigo@gmail.com
 *
 * @group Test_Unit_Domain_Core_UuidObj
 * @group Test_Unit_Domain_Core
 * @group Test_Unit_Domain
 * @group Test_Unit
 * @group Test
 * @test
 */
class UuidObjTest extends TestCase
{
    /**
     * Test that two consecutive calls do not create the same Uuid
     */
    public function testCreateUuid()
    {
        $uuid1 = UuidObj::create();
        $uuid2 = UuidObj::create();

        $this->assertNotEquals($uuid1->toString(), $uuid2->toString());
    }

    /**
     * Test that an Uuid can be converted to a string and back to an Uuid
     */
    public function testConversions()
    {
        $uuid = UuidObj::create();
        $str = $uuid->toString();

        $this->assertEquals($str, UuidObj::fromString($str)->toString());
    }

    /**
     * Test that an exception is thrown when an invalid UUID is provided
     */
    public function testFromStringWhenInvalidUuid()
    {
        $this->expectException(UuidException::class);

        UuidObj::fromString('this-is-not-an-uuuid');
    }

    /**
     * Test that the Uuid format is valid
     */
    public function testUuidFormat()
    {
        $uuid = UuidObj::create();
        $str = $uuid->toString();

        $this->assertRegExp($this->getUuidRegExp(), $str);
    }

    /**
     * Test createNewUuid without params
     */
    public function testCreateNewUuidWithouParams()
    {
        $uuid = UuidObj::createNewUuid();

        $this->assertRegExp($this->getUuidRegExp(), $uuid->toString());
    }

    /**
     * Test createNewUuid copying from another Uuid
     */
    public function testCreateNewUuidCopyingFromAnotherUuid()
    {
        $uuid1 = UuidObj::create();
        $uuid2 = UuidObj::createNewUuid($uuid1);

        $this->assertNotSame($uuid1, $uuid2);
        $this->assertEquals($uuid1->toString(), $uuid2->toString());
    }

    /**
     * Test createNewUuid copying from a string
     */
    public function testCreateNewUuidCopyingFromAString()
    {
        $str = '64a5f5c8-8fa9-4cf2-b39b-d462e5f663c1';
        $uuid = UuidObj::createNewUuid($str);

        $this->assertEquals($str, $uuid->toString());
    }

    /**
     * Test createNewUuid from invalid string throws an exception
     */
    public function testCreateNewUuidFromInvalidStringThrowsAnException()
    {
        $this->expectException(UuidException::class);

        $str = '_something_';
        UuidObj::createNewUuid($str);
    }

    /**
     * Test createNewUuid copying from invalid scalar value throws an exception
     */
    public function testCreateNewUuidFromInvalidScalarValueThrowsAnException()
    {
        $this->expectException(UuidException::class);

        UuidObj::createNewUuid(15);
    }

    /**
     * Test createNewUuid copying from invalid object throws an exception
     */
    public function testCreateNewUuidFromInvalidObjectThrowsAnException()
    {
        $this->expectException(UuidException::class);

        UuidObj::createNewUuid(new \DateTime());
    }

    /**
     * Get the regular expression to test an UUID
     *
     * @return string
     */
    private function getUuidRegExp()
    {
        return '/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i';
    }
}
