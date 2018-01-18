<?php

namespace Davamigo\Infrastructure\Config;

/**
 * Configuration class for MongoDB
 *
 * @package Davamigo\Infrastructure\Config
 * @author davamigo@gmail.com
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
