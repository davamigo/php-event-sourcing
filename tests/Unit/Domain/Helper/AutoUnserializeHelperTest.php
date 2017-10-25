<?php

namespace Test\Unit\Domain\Helper;

use Davamigo\Domain\Core\Uuid\UuidObj;
use Davamigo\Domain\Helpers\AutoUnserializeHelper;
use PHPUnit\Framework\TestCase;

/**
 * Class AutoUnserializeHelperTest
 *
 * @package Test\Unit\Domain\Helper
 * @author davamigo@gmail.com
 *
 * @group Test_Unit_Domain_Helper_AutoUnserializeHelper
 * @group Test_Unit_Domain_Helper
 * @group Test_Unit_Domain
 * @group Test_Unit
 * @group Test
 * @test
 */
class AutoUnserializeHelperTest extends TestCase
{
    /**
     * Test AutoUnserializeHelperTest::serialize() happy path
     */
    public function testSerialize()
    {
        $obj = new class {
            public $int;
            public $string;
            public $datetime;
            public $uuid;
            public function __construct()
            {
                $this->int = 12;
                $this->string = '_a_string_';
                $this->datetime = new \DateTime();
                $this->uuid = UuidObj::create();
            }
        };

        $data = [
            'int' => 15,
            'string' => '_another_string_',
            'datetime' => '2000-12-31T23:59:59+00:00',
            'uuid' => '0f594cac-b7ee-11e7-83c1-5f8a8a446ff9'
        ];

        $expectedDateTime = \DateTime::createFromFormat(\DateTime::RFC3339, $data['datetime']);
        $expectedUuid = UuidObj::fromString($data['uuid']);

        $result = AutoUnserializeHelper::unserialize($obj, $data);
        $this->assertEquals($data['int'], $result->int);
        $this->assertEquals($data['string'], $result->string);
        $this->assertEquals($expectedDateTime, $result->datetime);
        $this->assertEquals($expectedUuid, $result->uuid);
    }
}
