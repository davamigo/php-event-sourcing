<?php

namespace Davamigo\Infrastructure\Core\EventStorage;

use Davamigo\Domain\Core\Event\Event;
use Davamigo\Domain\Core\Event\EventBase;
use Davamigo\Domain\Core\Serializable\SerializableException;
use Davamigo\Domain\Core\Serializable\SerializableTrait;
use Davamigo\Domain\Core\EventStorage\EventStorage;
use Davamigo\Domain\Core\EventStorage\EventStorageException;
use Davamigo\Domain\Helpers\AutoSerializeHelper;
use Davamigo\Infrastructure\Core\Helpers\MongoDBConfigurator;
use MongoDB\Client as MongoDBClient;
use MongoDB\Exception\Exception as MongoDBException;
use Psr\Log\LoggerInterface;

/**
 * Event storage implementation using mongoDB: The warehouse of the events
 *
 * @package Davamigo\Infrastructure\Core\EventStorage
 */
class MongoDBEventStorage implements EventStorage
{
    /**
     * @var MongoDBClient
     */
    protected $client;

    /**
     * @var MongoDBConfigurator
     */
    protected $config;

    /**
     * The monolog object to log events
     *
     * @var LoggerInterface
     */
    protected $logger = null;

    /**
     * MongoDBEventStorer constructor.
     *
     * @param MongoDBClient       $client The mongoDB client
     * @param MongoDBConfigurator $config Configuration object
     * @param LoggerInterface     $logger Monolog object
     */
    public function __construct(
        MongoDBClient $client,
        MongoDBConfigurator $config,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Stores the event in the event storage
     *
     * @param Event $event
     * @return EventStorage
     * @throws EventStorageException
     */
    public function storeEvent(Event $event): EventStorage
    {
        $data = $this->serializeEvent($event);
        $data['_id'] = $event->uuid()->toString();

        try {
            $database = $this->client->selectDatabase($this->config->getDefaultDatabase());
            $collection = $database->selectCollection($this->config->getDefaultCollection());
            $collection->insertOne($data);
        } catch (MongoDBException $exc) {
            $this->logger->error('MongoDB event storage exception: ' . $exc->getMessage());
            $this->logger->debug($exc);

            throw new EventStorageException('MongoDB event storage: error storing an event!', 0, $exc);
        }

        return $this;
    }

    /**
     * Serializes an event.
     *
     * @param Event $event
     * @return array
     * @throws EventStorageException
     */
    protected function serializeEvent(Event $event) : array
    {
        // Remove non-serializable objects from the metadata
        if ($event instanceof EventBase
            && array_key_exists(SerializableTrait::class, class_uses($event))) {
            $metadata = $event->metadata();
            foreach ($metadata as $key => $item) {
                if (!AutoSerializeHelper::isSerializable($item)) {
                    $event->addMetadata([ $key => get_class($item) . '::class']);
                }
            }
        }

        // Serialize
        try {
            $data = $event->serialize();
        } catch (SerializableException $exc) {
            $this->logger->error('MongoDB event storage: error serializing the event - ' . $exc->getMessage());
            $this->logger->debug($exc);

            throw new EventStorageException('MongoDB event storage: error serializing the event!', 0, $exc);
        }

        return $data;
    }
}