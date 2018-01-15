<?php

namespace Davamigo\Infrastructure\Core\EventHandler;

use Davamigo\Domain\Core\Event\Event;
use Davamigo\Domain\Core\Event\EventBase;
use Davamigo\Domain\Core\Serializable\SerializableException;
use Davamigo\Domain\Core\Serializable\SerializableTrait;
use Davamigo\Domain\Core\EventHandler\EventHandler;
use Davamigo\Domain\Core\EventHandler\EventHandlerException;
use Davamigo\Domain\Helpers\AutoSerializeHelper;
use Davamigo\Infrastructure\Core\Helpers\MongoDBConfigurator;
use MongoDB\Client as MongoDBClient;
use MongoDB\Exception\Exception as MongoDBException;
use Psr\Log\LoggerInterface;

/**
 * Event handler implementation to store events using mongoDB
 *
 * @package Davamigo\Infrastructure\Core\EventHandler
 * @author davamigo@gmail.com
 */
class MongoDBEventStorageHandler implements EventHandler
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
     * MongoDBEventStorageHandler constructor.
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
     * The __invoke method is called when a script tries to call an object as a function.
     *
     * @param Event $event
     * @return EventHandler
     * @throws EventHandlerException
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.invoke
     */
    public function __invoke(Event $event)
    {
        return $this->handleEvent($event);
    }

    /**
     * Stores the event in the event storage
     *
     * @param Event $event
     * @return EventHandler
     * @throws EventHandlerException
     */
    public function handleEvent(Event $event): EventHandler
    {
        $this->logger->info('MongoDBEventStorageHandler: Event received.', [ 'event' => $event ]);

        $data = $this->serializeEvent($event);
        $data['_id'] = $event->uuid()->toString();

        try {
            $database = $this->client->selectDatabase($this->config->getDefaultDatabase());
            $collection = $database->selectCollection($this->config->getDefaultCollection());
            $collection->insertOne($data);
        } catch (MongoDBException $exc) {
            $this->logger->error('MongoDBEventStorageHandler exception: ' . $exc->getMessage());
            $this->logger->debug($exc);

            throw new EventHandlerException('MongoDBEventStorageHandler: error storing an event!', 0, $exc);
        }

        return $this;
    }

    /**
     * Serializes an event.
     *
     * @param Event $event
     * @return array
     * @throws EventHandlerException
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
            $this->logger->error('MongoDBEventStorageHandler: error serializing the event - ' . $exc->getMessage());
            $this->logger->debug($exc);

            throw new EventHandlerException('MongoDBEventStorageHandler: error serializing the event!', 0, $exc);
        }

        return $data;
    }
}
