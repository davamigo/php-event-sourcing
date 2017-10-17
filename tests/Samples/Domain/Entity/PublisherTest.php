<?php
namespace Test\Samples\Domain\Entity;

use Davamigo\Domain\Core\UuidObj;
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

    public function testPublisherSerialize()
    {
        $data = [
            'uuid' => UuidObj::create(),
            'name' => '_a_publisher_'
        ];

        /** @var Publisher $publisher */
        $publisher = Publisher::create($data);
        $result = $publisher->serialize();

        $this->assertEquals($data, $result);
    }
}
