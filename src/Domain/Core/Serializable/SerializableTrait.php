<?php

namespace Davamigo\Domain\Core\Serializable;

use Davamigo\Domain\Helpers\AutoSerializeException;
use Davamigo\Domain\Helpers\AutoSerializeHelper;

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
     * @throws SerializableException
     */
    public function serialize() : array
    {
        try {
            return AutoSerializeHelper::serialize($this);
        } catch (AutoSerializeException $exc) {
            throw new SerializableException('An error occurred serializing the object!', 0, $exc);
        }
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
}
