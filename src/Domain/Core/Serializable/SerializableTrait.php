<?php

namespace Davamigo\Domain\Core\Serializable;

use Davamigo\Domain\Helper\AutoSerializeException;
use Davamigo\Domain\Helper\AutoSerializeHelper;
use Davamigo\Domain\Helper\AutoUnserializeException;
use Davamigo\Domain\Helper\AutoUnserializeHelper;

/**
 * Trait SerializableTrait
 *
 * @package Davamigo\Domain\Core\Serializable
 * @author davamigo@gmail.com
 */
trait SerializableTrait
{
    /**
     * Creates a serializable object from an array
     *
     * @param array $data
     * @return Serializable
     * @throws SerializableException
     */
    public static function create(array $data) : Serializable
    {
        /** @var Serializable $obj */
        $obj = new self();

        try {
            $obj = AutoUnserializeHelper::unserialize($obj, $data);
        } catch (AutoUnserializeException $exc) {
            throw new SerializableException('An error occurred unserializing the object!', 0, $exc);
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
}
