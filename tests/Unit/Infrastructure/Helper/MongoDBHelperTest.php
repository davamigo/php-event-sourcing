<?php

namespace Test\Unit\Infrastructure\Helper;

use Davamigo\Infrastructure\Helper\MongoDBHelper;
use MongoDB\Driver\Cursor;
use MongoDB\Model\BSONDocument;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test of class Davamigo\Infrastructure\Helper\MongoDBHelper
 *
 * @package Test\Unit\Infrastructure\Helper
 * @author davamigo@gmail.com
 *
 * @group Test_Unit_Infrastructure_Helper_MongoDB
 * @group Test_Unit_Infrastructure_Helper
 * @group Test_Unit_Infrastructure
 * @group Test_Unit
 * @group Test
 * @test
 */
class MongoDBHelperTest extends TestCase
{
    /**
     * MongoDBHelper::bsonDocumentToArray()
     */
    public function testBsonDocumentToArrayWhenEmptyObject()
    {
        $obj = new BSONDocument();

        $expected = [];

        $result = MongoDBHelper::bsonDocumentToArray($obj);

        $this->assertEquals($expected, $result);
    }

    /**
     * MongoDBHelper::bsonDocumentToArray()
     */
    public function testBsonDocumentToArrayWhenScalarValues()
    {
        $data = [
            'str' => '101',
            'int' => 102
        ];

        $expected = $data;

        $obj = new BSONDocument($data);

        $result = MongoDBHelper::bsonDocumentToArray($obj);

        $this->assertEquals($expected, $result);
    }

    /**
     * MongoDBHelper::bsonDocumentToArray()
     */
    public function testBsonDocumentToArrayWhenBsonValues()
    {
        $data = [
            'str' => '101',
            'int' => 102,
            'obj' => new BSONDocument([
                'in' => '201'
            ])
        ];

        $expected = [
            'str' => '101',
            'int' => 102,
            'obj' => [
                'in' => '201'
            ]
        ];

        $obj = new BSONDocument($data);

        $result = MongoDBHelper::bsonDocumentToArray($obj);

        $this->assertEquals($expected, $result);
    }
}
