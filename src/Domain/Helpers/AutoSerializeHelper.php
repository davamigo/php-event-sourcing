<?php

namespace Davamigo\Domain\Helpers;

use Davamigo\Domain\Core\Serializable\Serializable;
use Davamigo\Domain\Core\Uuid\Uuid;

/**
 * Helper class to auto serialize an entity using reflexion
 *
 * @package Davamigo\Domain\Helpers
 * @author davamigo@gmail.com
 */
class AutoSerializeHelper
{
    /**
     * Serializes an object to an array
     *
     * @param object $obj
     * @return array
     * @throws AutoSerializeException
     */
    public static function serialize($obj) : array
    {
        if (!is_object($obj)) {
            throw new AutoSerializeException('The param must be an object!');
        }

        $class = new \ReflectionClass(get_class($obj));

        return static::serializeClass($obj, $class);
    }

    /**
     * Gets all the properties of a class and it's base classes to an array
     *
     * @param object $obj
     * @param \ReflectionClass $class
     * @return array
     * @throws AutoSerializeException
     */
    private static function serializeClass($obj, \ReflectionClass $class)
    {
        $data = [];

        /** @var \ReflectionProperty[] $properties */
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($obj);
            $data[$property->getName()] = static::serializeProperty($value);
        }

        $parent = $class->getParentClass();
        if ($parent) {
            $data += static::serializeClass($obj, $parent);
        }

        return $data;
    }

    /**
     * Serializes a single value by converting to an scalar value (string, int, bool, ...)
     *
     * @param mixed $property
     * @return array|string|int|bool|null
     * @throws AutoSerializeException
     */
    private static function serializeProperty($property)
    {
        if ($property instanceof Serializable) {
            return $property->serialize();
        }

        if ($property instanceof Uuid) {
            return $property->toString();
        }

        if ($property instanceof \DateTime) {
            return $property->format(\DateTime::RFC3339);
        }

        if (is_array($property)) {
            $data = [];
            foreach ($property as $key => $subvalue) {
                $data[$key] = static::serializeProperty($subvalue);
            }
            return $data;
        }

        if (is_object($property)) {
            throw new AutoSerializeException('The class ' . get_class($property) . ' is not serializable!');
        }

        return $property;
    }
}
