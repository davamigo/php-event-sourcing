<?php

namespace Davamigo\Infrastructure\Core\Storage;

use Davamigo\Domain\Core\Event\Event;
use Davamigo\Domain\Core\Event\EventBase;
use Davamigo\Domain\Core\Serializable\SerializableException;
use Davamigo\Domain\Core\Serializable\SerializableTrait;
use Davamigo\Domain\Core\Storage\EventStorer;
use Davamigo\Domain\Core\Storage\EventStorerException;
use Davamigo\Domain\Helpers\AutoSerializeHelper;
use MongoDB\Client as MongoDBClient;
use MongoDB\Exception\Exception as MongoDBException;
use Psr\Log\LoggerInterface;

/**
 * Event storer implementation using mongoDB: The warehouse of the events
 *
 * @package Davamigo\Infrastructure\Core\Storage
 */
class MongoDBEventStorer implements EventStorer
{
    /**
     * @var MongoDBClient
     */
    protected $client;

    /**
     * The monolog object to log events
     *
     * @var LoggerInterface
     */
    protected $logger = null;

    /**
     * MongoDBEventStorer constructor.
     *
     * @param MongoDBClient   $client  The mongoDB client
     * @param LoggerInterface $logger  Monolog object
     */
    public function __construct(
        MongoDBClient $client,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * Stores the event in the event storage
     *
     * @param Event $event
     * @return EventStorer
     * @throws EventStorerException
     */
    public function storeEvent(Event $event): EventStorer
    {
        if ($event instanceof EventBase
            && $event instanceof SerializableTrait) {
            $metadata = $event->metadata();
            foreach ($metadata as $key => $item) {
                if (!AutoSerializeHelper::isSerializable($item)) {
                    $event->addMetadata([ $key => get_class($item)]);
                }
            }
        }

        try {
            $data = $event->serialize();
            $data['_id'] = $event->uuid();
        } catch (SerializableException $exc) {
            $this->logger->error('MongoDB event storer exception: error serializing the event!');
            throw new EventStorerException('MongoDB event storer: error serializing the event!', 0, $exc);
        }

        try {
            $database = $this->client->selectDatabase($this->getDefaultDatabase());
            $collection = $database->selectCollection($this->getDefaultCollection());
            $collection->insertOne($data);
        } catch (MongoDBException $exc) {
            $this->logger->error('MongoDB event storer exception: ' . get_class($exc));
            throw new EventStorerException('MongoDB event storer: error storing an event!', 0, $exc);
        }

        return $this;
    }

    /**
     * Returns the default database name
     *
     * @return string
     */
    protected function getDefaultDatabase() : string
    {
        return 'events';
    }

    /**
     * Returns the default collection name
     *
     * @return string
     */
    protected function getDefaultCollection() : string
    {
        return 'storage';
    }
}