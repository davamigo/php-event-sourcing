<?php

namespace Test\Unit\Domain\Helper;

use Davamigo\Domain\Core\Entity\EntityBase;
use Davamigo\Domain\Core\Serializable\Serializable;
use Davamigo\Domain\Core\Uuid\UuidObj;
use Davamigo\Domain\Helper\AutoSerializeHelper;
use Davamigo\Domain\Helper\AutoUnserializeException;
use Davamigo\Domain\Helper\AutoUnserializeHelper;
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
     * Test AutoUnserializeHelperTest::unserialize() scalar objects
     */
    public function testUnserializeWithScalarObjects()
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

        $data = [
            'int' => 15,
            'string' => '_another_string_'
        ];

        $result = AutoUnserializeHelper::unserialize($obj, $data);
        $this->assertEquals($data['int'], $result->int);
        $this->assertEquals($data['string'], $result->string);
    }

    /**
     * Test AutoUnserializeHelperTest::unserialize() with an array
     */
    public function testUnserializeWithAnArray()
    {
        $obj = new class {
            public $array;
            public function __construct()
            {
                $this->array = [];
            }
        };

        $data = [
            'array' => [
                'e1',
                'e2',
                'e3'
            ]
        ];

        $result = AutoUnserializeHelper::unserialize($obj, $data);
        $this->assertEquals($data['array'], $result->array);
    }

    /**
     * Test AutoUnserializeHelperTest::unserialize() with DateTime object
     */
    public function testUnserializeWithDateTime()
    {
        $obj = new class {
            public $datetime;
            public function __construct()
            {
                $this->datetime = new \DateTime();
            }
        };

        $data = [
            'datetime' => '2000-12-31T23:59:59+00:00'
        ];

        $expectedDateTime = \DateTime::createFromFormat(\DateTime::RFC3339, $data['datetime']);

        $result = AutoUnserializeHelper::unserialize($obj, $data);
        $this->assertEquals($expectedDateTime, $result->datetime);
    }

    /**
     * Test AutoUnserializeHelperTest::unserialize() with UUID object
     */
    public function testUnserializeWithUUID()
    {
        $obj = new class {
            public $uuid1;
            public $uuid2;
            public function __construct()
            {
                $this->uuid1 = UuidObj::create();
                $this->uuid2 = UuidObj::create();
            }
        };

        $uuid = '0f594cac-b7ee-11e7-83c1-5f8a8a446ff9';

        $data = [
            'uuid1' => $uuid,
            'uuid2' => null
        ];

        $result = AutoUnserializeHelper::unserialize($obj, $data);
        $this->assertEquals(UuidObj::fromString($uuid), $result->uuid1);
        $this->assertNotNull(UuidObj::fromString($result->uuid2->toString()));
    }

    /**
     * Test AutoUnserializeHelperTest::unserialize() when property found in parent
     */
    public function testUnserializeWhenPropertyFoundInParent()
    {
        $obj = new class extends EntityBase {
            public $string;
            public static function create(array $data) : Serializable
            {
                $obj = new self();
                return AutoUnserializeHelper::unserialize($obj, $data);
            }
            public function serialize() : array
            {
                return AutoSerializeHelper::serialize($this);
            }
        };

        $data = [
            'string' => '_testing_property_in_parent__',
            'uuid' => 'ffffeeee-dddd-cccc-bbbb-aaaa99998888'
        ];

        $result = AutoUnserializeHelper::unserialize($obj, $data);
        $this->assertEquals('_testing_property_in_parent__', $result->string);
        $this->assertEquals('ffffeeee-dddd-cccc-bbbb-aaaa99998888', $result->uuid()->toString());
    }

    /**
     * Test AutoSerializeHelper::unserialize() when an inner serializable object
     */
    public function testUnserializeInnerObject()
    {
        $obj = new class {
            public $inner;
            public function __construct()
            {
                $this->inner = new class implements Serializable{
                    public static function create(array $data): Serializable
                    {
                        $obj = new self();
                        return AutoUnserializeHelper::unserialize($obj, $data);
                    }
                    public function serialize(): array
                    {
                        return AutoSerializeHelper::serialize($this);
                    }
                    public $string;
                    public function __construct()
                    {
                        $this->string = '';
                    }
                };
            }
        };

        $data = [
            'inner' => [
                'string' => '_testing_inner_object_'
            ]
        ];

        $result = AutoUnserializeHelper::unserialize($obj, $data);
        $this->assertInstanceOf(Serializable::class, $result->inner);
        $this->assertEquals('_testing_inner_object_', $result->inner->string);
    }

    /**
     * Test AutoUnserializeHelperTest::unserialize() when propery not found and the class has parent
     */
    public function testUnserializeWhenProperyNotFoundAndHasParent()
    {
        $obj = new class extends EntityBase {
            public static function create(array $data) : Serializable
            {
                $obj = new self();
                return AutoUnserializeHelper::unserialize($obj, $data);
            }
            public function serialize() : array
            {
                return AutoSerializeHelper::serialize($this);
            }
        };

        $data = [
            'string' => '_doesnt_matter_'
        ];

        $this->expectException(AutoUnserializeException::class);
        AutoUnserializeHelper::unserialize($obj, $data);
    }

    /**
     * Test AutoUnserializeHelperTest::unserialize() when property not found and the class hasn't parent
     */
    public function testUnserializeWhenProperyNotFoundAndHasNoParent()
    {
        $obj = new class {
        };

        $data = [
            'string' => '_doesnt_matter_'
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

        /** @var object $obj */
        $obj = '_non_object_';

        AutoUnserializeHelper::unserialize($obj, ['_something_']);
    }

    /**
     * Test AutoSerializeHelper::unserialize() an array with non array value
     */
    public function testUnserializeArrayWithNonArray()
    {
        $this->expectException(AutoUnserializeException::class);

        $obj = new class {
            public $array;
            public function __construct()
            {
                $this->array = [];
            }
        };

        $data = [
            'array' => null
        ];

        AutoUnserializeHelper::unserialize($obj, $data);
    }

    /**
     * Test AutoSerializeHelper::unserialize() when no unserializable object
     */
    public function testUnserializeNoUnserializableObeject()
    {
        $this->expectException(AutoUnserializeException::class);

        $obj = new class {
            public $obj;
            public function __construct()
            {
                $this->obj = new \stdClass();
            }
        };

        $data = [
            'obj' => null
        ];

        AutoUnserializeHelper::unserialize($obj, $data);
    }
}
