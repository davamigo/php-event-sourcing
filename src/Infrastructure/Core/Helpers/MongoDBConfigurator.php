<?php

namespace Davamigo\Infrastructure\Core\Helpers;

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
}
