<?php
namespace Test\Samples\Domain\Entity;

use Davamigo\Domain\Core\Serializable\SerializableException;
use Davamigo\Domain\Core\Uuid\UuidObj;
use Samples\Domain\Entity\Publisher;
use PHPUnit\Framework\TestCase;

/**
 * Test of class Samples\Domain\Entity\Publisher
 *
 * @package Samples\Domain\Entity
 * @author davamigo@gmail.com
 *
 * @group Test_Samples_Domain_Entity_Publisher
 * @group Test_Samples_Domain_Entity
 * @group Test_Samples_Domain
 * @group Test_Samples
 * @group Test
 * @test
 */
class PublisherTest extends TestCase
{
    /**
     * Test Publisher::create() function
     */
    public function testPublisherCreate()
    {
        $data = [
            'uuid' => UuidObj::create(),
            'name' => '_a_publisher_'
        ];

        /** @var Publisher $publisher */
        $publisher = Publisher::create($data);

        $this->assertEquals($data['uuid'], $publisher->uuid());
        $this->assertEquals($data['name'], $publisher->name());
    }

    /**
     * Test Publisher::create() function
     */
    public function testPublisherCreateWhenInvalidParamsProvided()
    {
        $this->expectException(SerializableException::class);

        $data = [
            'uuid' => UuidObj::create(),
            'name' => '_the_name_',
            'test' => '_another_field_'
        ];

        Publisher::create($data);
    }
    /**
     * Test Publisher::serialize() function
     */
    public function testPublisherSerialize()
    {
        $data = [
            'uuid' => UuidObj::create(),
            'name' => '_another_publisher_'
        ];

        /** @var Publisher $publisher */
        $publisher = Publisher::create($data);
        $result = $publisher->serialize();

        $this->assertEquals($data, $result);
    }
}
