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
     */
    final public static function cursorToArray(Cursor $cursor) : array
    {
        $result = [];
        $data = $cursor->toArray();
        foreach ($data as $key => $item) {
            if ($item instanceof BSONDocument) {
                $result[$key] = self::bsonDocumentToArray($item);
            } else {
                $result[$key] = $item;
            }
        }
        return $result;
    }

    /**
     * Convert MongoDB BSON document to an array
     *
     * @param BSONDocument $document
     * @return array
     */
    final public static function bsonDocumentToArray(BSONDocument $document) : array
    {
        $result = [];
        $data = $document->getArrayCopy();
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
