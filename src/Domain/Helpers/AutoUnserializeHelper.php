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
     * @throws AutoSerializeException
     */
    public static function unserialize($obj, array $data)
    {
        if (!is_object($obj)) {
            throw new AutoSerializeException('The param $obj must be an object!');
        }

        $class = new \ReflectionClass(get_class($obj));
        foreach ($data as $field => $rawValue) {
            // Find the property in the class and in the parents (recursive)
            $property = static::getPropReflectedRecursive($class, $field);
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
    private static function getPropReflectedRecursive(\ReflectionClass $class, string $field) : \ReflectionProperty
    {
        try {
            return $class->getProperty($field);
        } catch (\ReflectionException $exc) {
            $parent = $class->getParentClass();
            if ($parent) {
                try {
                    return self::getPropReflectedRecursive($parent, $field);
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
     */
    private static function getNewPropertyValue($baseType, $rawValue)
    {
        if ($baseType instanceof Serializable) {
            return $baseType::create($rawValue);
        }

        if ($baseType instanceof Uuid) {
            return UuidObj::fromString($rawValue);
        }

        if ($baseType instanceof \DateTime) {
            return \DateTime::createFromFormat(\DateTime::RFC3339, $rawValue);
        }

        // TODO unserialize arrays

        return $rawValue;
    }
}