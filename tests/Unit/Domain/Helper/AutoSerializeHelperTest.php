<?php

namespace Test\Unit\Domain\Helper;

use Davamigo\Domain\Core\Serializable\Serializable;
use Davamigo\Domain\Core\Uuid\UuidObj;
use Davamigo\Domain\Helpers\AutoSerializeException;
use Davamigo\Domain\Helpers\AutoSerializeHelper;
use PHPUnit\Framework\TestCase;

/**
 * Class AutoSerializeHelperTest
 *
 * @package Test\Unit\Domain\Helper
 * @author davamigo@gmail.com
 *
 * @group Test_Unit_Domain_Helper_AutoSerializeHelper
 * @group Test_Unit_Domain_Helper
 * @group Test_Unit_Domain
 * @group Test_Unit
 * @group Test
 * @test
 */
class AutoSerializeHelperTest extends TestCase
{
    /**
     * Test AutoSerializeHelper::serialize() happy path
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
                $this->datetime = \DateTime::createFromFormat(\DateTime::RFC3339, '2000-01-01T00:00:00+00:00');
                $this->uuid = UuidObj::fromString('0f594cac-b7ee-11e7-83c1-5f8a8a446ff9');
            }
        };

        $expected = [
            'int' => 12,
            'string' => '_a_string_',
            'datetime' => '2000-01-01T00:00:00+00:00',
            'uuid' => '0f594cac-b7ee-11e7-83c1-5f8a8a446ff9'
        ];

        $result = AutoSerializeHelper::serialize($obj);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test AutoSerializeHelper::serialize() special cases
     */
    public function testSerializeComplex()
    {
        $obj = new class implements Serializable {
            public static function create(array $data): Serializable
            {
                return new static();
            }
            public function serialize(): array
            {
                return AutoSerializeHelper::serialize($this);
            }
            public $inner;
            public function __construct()
            {
                $this->inner = new class implements Serializable{
                    public static function create(array $data): Serializable
                    {
                        return new static();
                    }
                    public function serialize(): array
                    {
                        return AutoSerializeHelper::serialize($this);
                    }
                    private $string;
                    private $array;
                    public function __construct()
                    {
                        $this->string = '_hello_world_';
                        $this->array = [ 10, 11, 12 ];
                    }
                };
            }
        };

        $expected = [
            'inner' => [
                'string' => '_hello_world_',
                'array' => [ 10, 11, 12 ]
            ]
        ];

        $result = $obj->serialize();
        $this->assertEquals($expected, $result);
    }

    /**
     * Test AutoSerializeHelper::serialize() when a non serializable property found
     */
    public function testSerializeWhenNonSerializableProperyFound()
    {
        $this->expectException(AutoSerializeException::class);

        $obj = new class {
            public $prop;
            public function __construct()
            {
                $this->prop = new \stdClass();
            }
        };

        AutoSerializeHelper::serialize($obj);
    }

    /**
     * Test AutoSerializeHelper::serialize() when an invalid object passed
     */
    public function testSerializeWhenInvalidObjectPassed()
    {
        $this->expectException(AutoSerializeException::class);

        /** @var \stdClass $obj */
        $obj = '_something_else_';

        AutoSerializeHelper::serialize($obj);
    }
}
