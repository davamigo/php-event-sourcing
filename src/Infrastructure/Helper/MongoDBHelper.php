<?php

namespace Davamigo\Infrastructure\Helper;

use MongoDB\Driver\Cursor;
use MongoDB\Model\BSONDocument;

/**
 * Helper class for MongoDB
 *
 * @package Davamigo\Infrastructure\Helper
 * @author davamigo@gmail.com
 */
class MongoDBHelper
{
    /**
     * Convert MongoDB cursor to an array
     *
     * @param Cursor $cursor
     * @return array
     * @codeCoverageIgnore
     */
    final public static function cursorToArray(Cursor $cursor) : array
    {
        $data = $cursor->toArray();
        return self::parse($data);
    }

    /**
     * Convert MongoDB BSON document to an array
     *
     * @param BSONDocument $document
     * @return array
     */
    final public static function bsonDocumentToArray(BSONDocument $document) : array
    {
        $data = $document->getArrayCopy();
        return self::parse($data);
    }

    /**
     * Parses array data to convert BSON objects to array
     *
     * @param array $data
     * @return array
     */
    final private static function parse(array $data) : array
    {
        $result= [];
        foreach ($data as $key => $item) {
            if ($item instanceof BSONDocument) {
                $result[$key] = self::bsonDocumentToArray($item);
            } else {
                $result[$key] = $item;
            }
        }
        return $result;
    }
}
