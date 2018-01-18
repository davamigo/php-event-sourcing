<?php

namespace Test\Samples\Domain\Entity\Custom;

use Samples\Domain\Entity\Custom\Publisher;
use Test\Samples\Domain\Entity\PublisherTest as BasePublisherTest;

/**
 * Test of class Samples\Domain\Entity\Publisher
 *
 * @package Test\Samples\Domain\Entity\Custom
 * @author davamigo@gmail.com
 *
 * @group Test_Samples_Domain_Entity_Publisher_Custom
 * @group Test_Samples_Domain_Entity_Publisher
 * @group Test_Samples_Domain_Entity
 * @group Test_Samples_Domain
 * @group Test_Samples
 * @group Test
 * @test
 */
class PublisherTest extends BasePublisherTest
{
    /**
     * Creates the publisher object
     *
     * @param array $data
     * @return Publisher
     */
    protected function createPublisher(array $data)
    {
        return Publisher::create($data);
    }
}
