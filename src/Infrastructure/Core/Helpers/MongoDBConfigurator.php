<?php

namespace Davamigo\Infrastructure\Core\Helpers;

use MongoDB\Driver\Cursor;
use MongoDB\Model\BSONDocument;

/**
 * Configuration class for MongoDB
 *
 * @package Davamigo\Infrastructure\Core\Helpers
 */
class MongoDBConfigurator
{
    /**
     * Returns the default database name
     *
     * @return string
     */
    public function getDefaultDatabase() : string
    {
        return 'events';
    }

    /**
     * Returns the default collection name
     *
     * @return string
     */
    public function getDefaultCollection() : string
    {
        return 'storage';
    }

    /**
     * Convert MongoDB cursor to an array
     *
     * @param Cursor $cursor
     * @return array
     */
    public static function cursorToArray(Cursor $cursor) : array
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
    public static function bsonDocumentToArray(BSONDocument $document) : array
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
