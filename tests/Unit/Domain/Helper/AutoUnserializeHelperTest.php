<?php

namespace Test\Unit\Domain\Helper;

use Davamigo\Domain\Core\Entity\EntityBase;
use Davamigo\Domain\Core\Serializable\Serializable;
use Davamigo\Domain\Core\Serializable\SerializableTrait;
use Davamigo\Domain\Core\Uuid\UuidObj;
use Davamigo\Domain\Helpers\AutoSerializeHelper;
use Davamigo\Domain\Helpers\AutoUnserializeException;
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
     * Test AutoUnserializeHelperTest::unserialize() happy path
     */
    public function testUnserialize()
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

    /**
     * Test AutoUnserializeHelperTest::unserialize() when propery found in parent
     */
    public function testUnserializeWhenPropertyFoundInParent()
    {
        $obj = new class extends EntityBase {
            use SerializableTrait;
        };

        $data = [
            'uuid' => '0f594cac-b7ee-11e7-83c1-5f8a8a446ff9'
        ];

        $result = AutoUnserializeHelper::unserialize($obj, $data);
        $this->assertEquals('0f594cac-b7ee-11e7-83c1-5f8a8a446ff9', $result->uuid()->toString());
    }

    /**
     * Test AutoUnserializeHelperTest::unserialize() when propery not found and the class has parent
     */
    public function testUnserializeWhenProperyNotFoundAndHasParent()
    {
        $obj = new class extends EntityBase {
            use SerializableTrait;
        };

        $data = [
            'str' => '_a_str_'
        ];

        $this->expectException(AutoUnserializeException::class);
        AutoUnserializeHelper::unserialize($obj, $data);
    }

    /**
     * Test AutoUnserializeHelperTest::unserialize() when propery not found and the class hasn't parent
     */
    public function testUnserializeWhenProperyNotFoundAndHasNoParent()
    {
        $obj = new class {
        };

        $data = [
            'str' => '_a_str_'
        ];

        $this->expectException(AutoUnserializeException::class);
        AutoUnserializeHelper::unserialize($obj, $data);
    }

    /**
     * Test AutoUnserializeHelperTest::unserialize() when non-object provided
     */
    public function testUnserializeWhenNonObjectProvided()
    {
        $this->expectException(AutoUnserializeException::class);

        AutoUnserializeHelper::unserialize('_non_object_', ['_something_']);
    }
}
