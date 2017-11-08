<?php

namespace Davamigo\Domain\Helpers;

use Davamigo\Domain\Core\Serializable\Serializable;
use Davamigo\Domain\Core\Uuid\Uuid;
use Davamigo\Domain\Core\Uuid\UuidObj;

/**
 * Helper class to auto unserialize an entity using reflexion
 *
 * @package Davamigo\Domain\Helpers
 * @author davamigo@gmail.com
 */
class AutoUnserializeHelper
{
    /**
     * Serializes an object to an array
     *
     * @param object $obj
     * @param array $data
     * @return object
     * @throws AutoUnserializeException
     */
    public static function unserialize($obj, array $data)
    {
        if (!is_object($obj)) {
            throw new AutoUnserializeException('The param $obj must be an object!');
        }

        $class = new \ReflectionClass(get_class($obj));
        foreach ($data as $field => $rawValue) {
            // Find the property in the class and in the parents (recursive)
            $property = static::findProperty($class, $field);
            $property->setAccessible(true);

            // Get the old value of the property (to find out the type or class)
            $oldValue = $property->getValue($obj);

            // Convert the raw value to the same type or class than the old value
            $newValue = static::getNewPropertyValue($oldValue, $rawValue);

            // Set the new propery value
            $property->setValue($obj, $newValue);
        }

        return $obj;
    }

    /**
     * Gets a property by name from the class and it's bases classes
     *
     * @param \ReflectionClass $class
     * @param string $field
     * @return \ReflectionProperty
     * @throws AutoUnserializeException
     */
    private static function findProperty(\ReflectionClass $class, string $field) : \ReflectionProperty
    {
        try {
            return $class->getProperty($field);
        } catch (\ReflectionException $exc) {
            $parent = $class->getParentClass();
            if ($parent) {
                try {
                    return self::findProperty($parent, $field);
                } catch (AutoUnserializeException $subExc) {
                    // Do nothing
                }
            }

            throw new AutoUnserializeException('Property ' . $field . ' does not exist in ' . self::class, 0, $exc);
        }
    }

    /**
     * Convert the raw value to the same type or class than the vase type
     *
     * @param mixed $baseType
     * @param mixed $rawValue
     * @return mixed
     * @throws AutoUnserializeException
     */
    private static function getNewPropertyValue($baseType, $rawValue)
    {
        if (is_object($baseType)) {
            return static::getNewObjectPropertyValue($baseType, $rawValue);
        }

        if (is_array($baseType)) {
            return static::getNewArrayPropertyValue($baseType, $rawValue);
        }

        return $rawValue;
    }

    /**
     * Convert the raw value to the same type or class than the vase type
     *
     * @param mixed $baseType
     * @param mixed $rawValue
     * @return mixed
     * @throws AutoUnserializeException
     */
    private static function getNewObjectPropertyValue($baseType, $rawValue)
    {
        if ($baseType instanceof Serializable) {
            return $baseType::create($rawValue);
        }

        if ($baseType instanceof Uuid) {
            if (null === $rawValue) {
                $uuid = UuidObj::create();
            } else {
                $uuid = UuidObj::fromString($rawValue);
            }
            return $uuid;
        }

        if ($baseType instanceof \DateTime) {
            return \DateTime::createFromFormat(\DateTime::RFC3339, $rawValue);
        }

        throw new AutoUnserializeException('The class ' . get_class($baseType) . ' is not unserializable!');
    }

    /**
     * Convert the raw value to the same type or class than the vase type
     *
     * @param array $baseArray
     * @param mixed $valuesArray
     * @return mixed
     * @throws AutoUnserializeException
     */
    private static function getNewArrayPropertyValue(array $baseArray, $valuesArray)
    {
        if (!is_array($valuesArray)) {
            throw new AutoUnserializeException('The value of the property must be an array!');

        }

        $result = [];
        $baseObj = reset($baseArray);
        foreach ($valuesArray as $key => $subvalue) {
            if (null === $baseObj) {
                $result[$key] = $subvalue;
            } else {
                $result[$key] = static::getNewPropertyValue($baseObj, $subvalue);
            }
        }

        return $result;
    }
}
