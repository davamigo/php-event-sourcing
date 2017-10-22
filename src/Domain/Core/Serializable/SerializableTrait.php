<?php

namespace Davamigo\Domain\Core\Serializable;

use Davamigo\Domain\Core\Uuid\Uuid;

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
            $value = $property->getValue($this);
            $data[$property->getName()] = $this->getSerializedPropertyValue($value);
        }

        $parent = $class->getParentClass();
        if ($parent) {
            $data += $this->getPropsReflectedRecursive($parent);
        }

        return $data;
    }

    /**
     * Serializes a single value by converting to an scalar value (string, int, bool, ...)
     *
     * @param $value
     * @return array|string|int|bool|null
     * @throws SerializableException
     */
    private function getSerializedPropertyValue($value)
    {
        if ($value instanceof Serializable) {
            return $value->serialize();
        }

        if ($value instanceof Uuid) {
            return $value->toString();
        }

        if ($value instanceof \DateTime) {
            return $value->format(\DateTime::RFC3339);
        }

        if (is_array($value)) {
            $data = [];
            foreach ($value as $key => $subvalue) {
                $data[$key] = $this->getSerializedPropertyValue($subvalue);
            }
            return $data;
        }

        if (is_object($value)) {
            throw new SerializableException('The class ' . get_class($value) . ' is not serializable!');
        }

        return $value;
    }
}
