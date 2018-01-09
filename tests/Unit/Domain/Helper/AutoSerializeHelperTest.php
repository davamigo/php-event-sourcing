<?php

namespace Test\Unit\Domain\Helper;

use Davamigo\Domain\Core\Entity\EntityBase;
use Davamigo\Domain\Core\Serializable\Serializable;
use Davamigo\Domain\Core\Serializable\SerializableTrait;
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
     * Test AutoSerializeHelper::serialize() scalar objects
     */
    public function testSerializeWithScalarObjects()
    {
        $obj = new class {
            public $int;
            public $string;
            public function __construct()
            {
                $this->int = 12;
                $this->string = '_a_string_';
            }
        };

        $expected = [
            'int' => 12,
            'string' => '_a_string_'
        ];

        $result = AutoSerializeHelper::serialize($obj);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test AutoSerializeHelper::serialize() an array
     */
    public function testSerializeWithAnArray()
    {
        $obj = new class {
            private $string;
            private $array;
            public function __construct()
            {
                $this->string = '_hello_world_';
                $this->array = [ 10, 11, 12 ];
            }
        };

        $expected = [
            'string' => '_hello_world_',
            'array' => [ 10, 11, 12 ]
        ];

        $result = AutoSerializeHelper::serialize($obj);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test AutoSerializeHelper::serialize() with DateTime object
     */
    public function testSerializeWithDateTime()
    {
        $obj = new class {
            public $datetime;
            public function __construct()
            {
                $this->datetime = \DateTime::createFromFormat(\DateTime::RFC3339, '2000-01-01T00:00:00+00:00');
            }
        };

        $expected = [
            'datetime' => '2000-01-01T00:00:00+00:00'
        ];

        $result = AutoSerializeHelper::serialize($obj);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test AutoSerializeHelper::serialize() with UUID object
     */
    public function testSerializeWithUUID()
    {
        $obj = new class {
            public $uuid;
            public function __construct()
            {
                $this->uuid = UuidObj::fromString('0f594cac-b7ee-11e7-83c1-5f8a8a446ff9');
            }
        };

        $expected = [
            'uuid' => '0f594cac-b7ee-11e7-83c1-5f8a8a446ff9'
        ];

        $result = AutoSerializeHelper::serialize($obj);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test AutoSerializeHelper::serialize() when an object implements Serializable interface
     */
    public function testSerializeWhenImplementsSerializable()
    {
        $obj = new class implements Serializable {
            public $string;
            public function __construct()
            {
                $this->string = '_testing_interface_';
            }
            public static function create(array $data): Serializable
            {
                return new self();
            }
            public function serialize(): array
            {
                return AutoSerializeHelper::serialize($this);
            }
        };

        $expected = [
            'string' => '_testing_interface_'
        ];

        /** @var Serializable $obj */
        $result = $obj->serialize();
        $this->assertEquals($expected, $result);
    }

    /**
     * Test AutoSerializeHelper::serialize() for a extended class
     */
    public function testSerializeForExtendedClass()
    {
        $obj = new class extends EntityBase {
            public $string;
            public function __construct()
            {
                parent::__construct('00000000-1111-2222-3333-444444444444');
                $this->string = '_testing_extension_';
            }
            public static function create(array $data) : Serializable
            {
                return new self();
            }
            public function serialize() : array
            {
                return AutoSerializeHelper::serialize($this);
            }
        };

        $expected = [
            'uuid' => '00000000-1111-2222-3333-444444444444',
            'string' => '_testing_extension_',
        ];

        /** @var EntityBase $obj */
        $result = $obj->serialize();
        $this->assertEquals($expected, $result);
    }

    /**
     * Test AutoSerializeHelper::serialize() when an inner serializable object
     */
    public function testSerializeInnerObject()
    {
        $obj = new class {
            public $inner;
            public function __construct()
            {
                $this->inner = new class implements Serializable {
                    public static function create(array $data): Serializable
                    {
                        return new self();
                    }
                    public function serialize(): array
                    {
                        return AutoSerializeHelper::serialize($this);
                    }
                    private $string;
                    public function __construct()
                    {
                        $this->string = '_testing_inner_';
                    }
                };
            }
        };

        $expected = [
            'inner' => [
                'string' => '_testing_inner_'
            ]
        ];

        $result = AutoSerializeHelper::serialize($obj);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test AutoSerializeHelper::serialize() when a non serializable property found
     */
    public function testSerializeWhenNonSerializablePropertyFound()
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

    /**
     * Test AutoSerializeHelper::isSerializable() for basic types
     */
    public function testIsSerializableBasicTypes()
    {
        $this->assertTrue(AutoSerializeHelper::isSerializable(101));
        $this->assertTrue(AutoSerializeHelper::isSerializable(0xFF));
        $this->assertTrue(AutoSerializeHelper::isSerializable(18.4));
        $this->assertTrue(AutoSerializeHelper::isSerializable("str"));
        $this->assertTrue(AutoSerializeHelper::isSerializable([]));
        $this->assertTrue(AutoSerializeHelper::isSerializable(['one', 'two']));
    }

    /**
     * Test AutoSerializeHelper::isSerializable() for serializable objects
     */
    public function testIsSerializableSerializableObjects()
    {
        $this->assertTrue(AutoSerializeHelper::isSerializable(new \DateTime()));
        $this->assertTrue(AutoSerializeHelper::isSerializable(UuidObj::create()));
        $this->assertTrue(AutoSerializeHelper::isSerializable(new class implements Serializable {
            use SerializableTrait;
        }));
    }

    /**
     * Test AutoSerializeHelper::isSerializable() for non serializable objects
     */
    public function testIsSerializableNonSerializable()
    {
        $this->assertFalse(AutoSerializeHelper::isSerializable(new \stdClass()));
        $this->assertFalse(AutoSerializeHelper::isSerializable(new class {}));
    }
}
