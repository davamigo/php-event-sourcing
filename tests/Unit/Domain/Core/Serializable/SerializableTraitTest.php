<?php

namespace Test\Unit\Domain\Core\Serializable;

use Davamigo\Domain\Core\Serializable\Serializable;
use Davamigo\Domain\Core\Serializable\SerializableException;
use Davamigo\Domain\Core\Serializable\SerializableTrait;
use Davamigo\Domain\Core\Uuid\UuidObj;
use PHPUnit\Framework\TestCase;

/**
 * Class SerializableTraitTest
 *
 * @package Test\Unit\Domain\Core
 * @author davamigo@gmail.com
 *
 * @group Test_Unit_Domain_Core_SerializableTrait
 * @group Test_Unit_Domain_Core
 * @group Test_Unit_Domain
 * @group Test_Unit
 * @group Test
 * @test
 */
class SerializableTraitTest extends TestCase
{
    /**
     * Test SerializableTrait::serialize() happy path
     */
    public function testSerialize()
    {
        $obj = new class implements Serializable {
            use SerializableTrait;
            public $int;
            public $string;
            public $datetime;
            public $uuid;
            public function __construct()
            {
                $this->int = 11;
                $this->string = '_anything_';
                $this->datetime = \DateTime::createFromFormat(\DateTime::RFC3339, '2000-01-01T00:00:00+00:00');
                $this->uuid = UuidObj::fromString('0f594cac-b7ee-11e7-83c1-5f8a8a446ff9');
            }
        };

        $expected = [
            'int' => 11,
            'string' => '_anything_',
            'datetime' => '2000-01-01T00:00:00+00:00',
            'uuid' => '0f594cac-b7ee-11e7-83c1-5f8a8a446ff9'
        ];

        $result = $obj->serialize();
        $this->assertEquals($expected, $result);
    }

    /**
     * Test SerializableTrait::serialize() when a non serializable property found
     */
    public function testSerializeWhenNonSerializablePropertyFound()
    {
        $this->expectException(SerializableException::class);

        $obj = new class implements Serializable {
            use SerializableTrait;
            public $prop;
            public function __construct()
            {
                $this->prop = new \stdClass();
            }
        };

        $obj->serialize();
    }

    /**
     * Test SerializableTrait::create() happy path
     */
    public function testCreate()
    {
        $obj = new class implements Serializable {
            use SerializableTrait;
            public $int;
            public $string;
            public $datetime;
            public $uuid;
            public function __construct()
            {
                $this->datetime = new \DateTime();
                $this->uuid = UuidObj::create();
            }
        };

        $data = [
            'int' => 101,
            'string' => '_text_',
            'datetime' => '2000-01-01T00:00:00+00:00',
            'uuid' => '00000000-2222-5555-9999-aaaaaaaaaaaa'
        ];

        $result = $obj::create($data);
        $this->assertEquals(101, $result->int);
        $this->assertEquals('_text_', $result->string);
        $this->assertEquals('2000-01-01T00:00:00+00:00', $result->datetime->format(\DateTime::RFC3339));
        $this->assertEquals('00000000-2222-5555-9999-aaaaaaaaaaaa', $result->uuid->toString());
    }

    /**
     * Test SerializableTrait::create() when a field does not exist
     */
    public function testCreateWhenFieldDoesNotExist()
    {
        $this->expectException(SerializableException::class);

        $obj = new class implements Serializable {
            use SerializableTrait;
            public $int;
            public $string;
        };

        $data = [
            'invalidProperty' => 1001
        ];

        $obj::create($data);
    }
}
