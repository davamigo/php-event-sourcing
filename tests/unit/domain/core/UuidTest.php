<?php

namespace Test\unit\domain\core;

use Davamigo\domain\core\UuidObj;
use PHPUnit\Framework\TestCase;

/**
 * Test of class Davamigo\domain\core\UuidObj
 *
 * @package Test\unit\domain\core
 * @author davamigo@gmail.com
 */
class UuidTest extends TestCase
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
     * Test that the Uuid format is valid
     */
    public function testUuidFormat()
    {
        $uuid = UuidObj::create();
        $str = $uuid->toString();

        $this->assertRegExp('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $str);
    }
}
