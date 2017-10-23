<?php

namespace Unit\Domain\Helper;

use Davamigo\Domain\Helpers\AutoSerializeException;
use Davamigo\Domain\Helpers\AutoSerializeHelper;
use PHPUnit\Framework\TestCase;

/**
 * Class AutoSerializeHelperTest
 *
 * @package Unit\Domain\Helper
 * @author davamigo@gmail.com
 *
 * @group Test_Unit_Domain_Core_SerializableTrait
 * @group Test_Unit_Domain_Core
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
            public function __construct() {
                $this->int = 12;
                $this->string = '_something_';
                $this->datetime = \DateTime::createFromFormat(\DateTime::RFC3339, '2000-01-01T00:00:00+00:00');
            }
        };

        $expected = [
            'int' => 12,
            'string' => '_something_',
            'datetime' => '2000-01-01T00:00:00+00:00'
        ];

        $result = AutoSerializeHelper::serialize($obj);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test AutoSerializeHelper::serialize() when an invalid object passed
     */
    public function testSerializeWhenInvalidObjectPassed()
    {
        $this->expectException(AutoSerializeException::class);

        $obj = '_something_';

        AutoSerializeHelper::serialize($obj);
    }

    /**
     * Test AutoSerializeHelper::serialize() when a non serializable property found
     */
    public function testSerializeWhenNonSerializableProperyFound()
    {
        $this->expectException(AutoSerializeException::class);

        $obj = new class {
            public $prop;
            public function __construct() {
                $this->prop = new \stdClass();
            }
        };

        AutoSerializeHelper::serialize($obj);
    }
}
