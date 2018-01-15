<?php

namespace Test\Unit\Infrastructure\Config;

use Davamigo\Infrastructure\Config\AmqpConfigurator;
use PHPUnit\Framework\TestCase;

/**
 * Test of class Davamigo\Infrastructure\Config\AmqpConfigurator
 *
 * @package Test\Unit\Infrastructure\Config
 * @author davamigo@gmail.com
 *
 * @group Test_Unit_Infrastructure_Config_Amqp
 * @group Test_Unit_Infrastructure_Config
 * @group Test_Unit_Infrastructure
 * @group Test_Unit
 * @group Test
 * @test
 */
class AmqpConfiguratorTest extends TestCase
{
    /**
     * AmqpConfigurator::getDefaultExchange()
     */
    public function testGetDefaultExchange()
    {
        $config = new AmqpConfigurator();
        $result = $config->getDefaultExchange();
        $this->assertNotEmpty($result);
        $this->assertTrue(is_string($result));
    }

    /**
     * AmqpConfigurator::getDefaultQueues()
     */
    public function testGetDefaultQueues()
    {
        $config = new AmqpConfigurator();
        $result = $config->getDefaultQueues();
        $this->assertTrue(is_array($result));
    }
}
