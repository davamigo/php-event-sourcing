<?php

namespace Test\Unit\Infrastructure\Core;

use PHPUnit\Framework\TestCase;

/**
 * Provides extra functions to the standards PHPUnit TestCase class
 *
 * @package Test\Unit\Infrastructure\Core
 * @author davamigo@gmail.com
 */
class AdvancedTestCase extends TestCase
{
    /**
     * Call private method
     *
     * @param object $object
     * @param string $method
     * @param array $args
     * @return mixed
     */
    final protected function callPrivateMethod($object, string $method, array $args = [])
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionMethod = $reflectionClass->getMethod($method);
        $reflectionMethod->setAccessible(true);
        return $reflectionMethod->invokeArgs($object, $args);
    }

    /**
     * Get the value of a private property of an object using reflection
     *
     * @param object $object
     * @param string $property
     * @return mixed
     */
    final protected function getPrivateProperty($object, string $property)
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($property);
        $reflectionProperty->setAccessible(true);
        return $reflectionProperty->getValue($object);
    }

    /**
     * Get the value of a private property of an object using reflection
     *
     * @param object $object
     * @param string $property
     * @param mixed  $value
     * @return void
     */
    final protected function setPrivateProperty($object, string $property, $value) : void
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
    }
}
