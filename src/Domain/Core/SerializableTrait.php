<?php

namespace Davamigo\Domain\Core;

use Davamigo\Domain\Core\Exception\SerializableException;

/**
 * Trait SerializableTrait
 *
 * @package Davamigo\Domain\Core
 * @author davamigo@gmail.com
 */
trait SerializableTrait
{
    /**
     * Creates a serializable object from an array
     *
     * @param array $data
     * @return Serializable
     */
    public static function create(array $data) : Serializable
    {
        /** @var Serializable $obj */
        $obj = new self();

        $class = new \ReflectionClass($obj);
        foreach ($data as $field => $value) {
            $property = self::getPropReflectedRecursive($class, $field);
            $property->setAccessible(true);
            $property->setValue($obj, $value);
        }

        return $obj;
    }

    /**
     * Serializes the object to an array
     *
     * @return array
     */
    public function serialize() : array
    {
        $class = new \ReflectionClass(self::class);

        return $this->getPropsReflectedRecursive($class);
    }

    /**
     * Gets a property by name from the class and it's bases classes
     *
     * @param \ReflectionClass $class
     * @param string $field
     * @return \ReflectionProperty
     * @throws SerializableException
     */
    private static function getPropReflectedRecursive(\ReflectionClass $class, string $field) : \ReflectionProperty
    {
        try {
            return $class->getProperty($field);
        } catch (\ReflectionException $exc) {
            $parent = $class->getParentClass();
            if ($parent) {
                try {
                    return self::getPropReflectedRecursive($parent, $field);
                } catch (SerializableException $subExc) {
                    // Do nothing
                }
            }

            throw new SerializableException('Property ' . $field . ' does not exist in ' . self::class, 0, $exc);
        }
    }

    /**
     * Gets all the properties of a class and it's base classes to an array
     *
     * @param \ReflectionClass $class
     * @return array
     */
    private function getPropsReflectedRecursive(\ReflectionClass $class)
    {
        $data = [];

        /** @var \ReflectionProperty[] $properties */
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $data[$property->getName()] = $property->getValue($this);
        }

        $parent = $class->getParentClass();
        if ($parent) {
            $data += $this->getPropsReflectedRecursive($parent);
        }

        return $data;
    }
}
