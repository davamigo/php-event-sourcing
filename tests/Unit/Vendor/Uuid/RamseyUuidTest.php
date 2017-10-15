<?php

namespace Test\Unit\Vendor\Uuid;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * Test of library ramsey/uuid
 *
 * @package Test\Unit\Vendor\Uuid
 * @author davamigo@gmail.com
 *
 * @group Test_Unit_Vendor_Uuid
 * @group Test_Unit_Vendor
 * @group Test_Unit
 * @group Test
 * @test
 */
class RamseyUuidTest extends TestCase
{
    /**
     * Test two consecutive calls make different UUIDs
     */
    public function testUuidGeneration()
    {
        $uuid1 = Uuid::uuid1();
        $uuid2 = Uuid::uuid1();

        $str1 = $uuid1->toString();
        $str2 = $uuid2->toString();

        $this->assertInternalType('string', $str1);
        $this->assertInternalType('string', $str2);
        $this->assertNotEquals($str1, $str2);
    }

    /**
     * Test Uuid conversion to string and from string
     */
    public function testUuidConversion()
    {
        $uuid1 = Uuid::uuid1();
        $str1 = $uuid1->toString();

        $uuid2 = Uuid::fromString($str1);
        $str2 = $uuid2->toString();

        $this->assertEquals($str1, $str2);
    }

    /**
     * Test Uuid format after converting to a string
     */
    public function testUuidFormat()
    {
        $uuid = Uuid::uuid1();
        $str = $uuid->toString();

        $this->assertRegExp('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $str);
    }
}
