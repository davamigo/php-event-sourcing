<?php

namespace Test\Unit\Infrastructure\Config;

use Davamigo\Infrastructure\Config\MongoDBConfigurator;
use PHPUnit\Framework\TestCase;

/**
 * Test of class Davamigo\Infrastructure\Config\MongoDBConfigurator
 *
 * @package Test\Unit\Infrastructure\Config
 * @author davamigo@gmail.com
 *
 * @group Test_Unit_Infrastructure_Config_MongoDB
 * @group Test_Unit_Infrastructure_Config
 * @group Test_Unit_Infrastructure
 * @group Test_Unit
 * @group Test
 * @test
 */
class MongoDBConfiguratorTest extends TestCase
{
    /**
     * MongoDBConfigurator::getDefaultExchange()
     */
    public function testGetDefaultExchange()
    {
        $config = new MongoDBConfigurator();
        $result = $config->getDefaultDatabase();
        $this->assertNotEmpty($result);
        $this->assertTrue(is_string($result));
    }

    /**
     * MongoDBConfigurator::getDefaultQueues()
     */
    public function testGetDefaultQueues()
    {
        $config = new MongoDBConfigurator();
        $result = $config->getDefaultCollection();
        $this->assertNotEmpty($result);
        $this->assertTrue(is_string($result));
    }
}
