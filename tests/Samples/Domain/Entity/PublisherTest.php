<?php
namespace Test\Samples\Domain\Entity;

use Davamigo\Domain\Core\Uuid\UuidObj;
use Samples\Domain\Entity\Publisher;
use PHPUnit\Framework\TestCase;

/**
 * Test of class Samples\Domain\Entity\Publisher
 *
 * @package Test\Samples\Domain\Entity
 * @author davamigo@gmail.com
 *
 * @group Test_Samples_Domain_Entity_Publisher
 * @group Test_Samples_Domain_Entity
 * @group Test_Samples_Domain
 * @group Test_Samples
 * @group Test
 * @test
 */
abstract class PublisherTest extends TestCase
{
    /**
     * Creates the publisher object
     *
     * @param array $data
     * @return Publisher
     */
    protected abstract function createPublisher(array $data);

    /**
     * Test Publisher::create() function
     */
    public function testPublisherCreate()
    {
        $data = [
            'uuid' => UuidObj::create()->toString(),
            'name' => '_some_publisher_'
        ];

        /** @var Publisher $publisher */
        $publisher = $this->createPublisher($data);

        $this->assertEquals($data['uuid'], $publisher->uuid()->toString());
        $this->assertEquals($data['name'], $publisher->name());
    }

    /**
     * Test Publisher::serialize() function
     */
    public function testPublisherSerialize()
    {
        $uuid = UuidObj::create();

        $data = [
            'uuid' => $uuid->toString(),
            'name' => '_another_publisher_'
        ];

        /** @var Publisher $publisher */
        $publisher = $this->createPublisher($data);
        $result = $publisher->serialize();

        $this->assertEquals($data, $result);
    }
}
