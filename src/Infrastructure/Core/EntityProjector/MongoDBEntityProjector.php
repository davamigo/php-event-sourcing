<?php

namespace Davamigo\Infrastructure\Core\EntityProjector;

use Davamigo\Domain\Core\Entity\Entity;
use Davamigo\Domain\Core\EntityProjector\EntityProjector;
use Davamigo\Domain\Core\EntityProjector\EntityProjectorErrorException;
use Davamigo\Domain\Core\EntityProjector\EntityProjectorNotFoundException;
use Davamigo\Domain\Core\Event\Event;
use Davamigo\Domain\Core\Event\EventBase;
use Davamigo\Domain\Core\Event\EventCollection;
use Davamigo\Domain\Core\Uuid\Uuid;
use Davamigo\Infrastructure\Core\Helpers\MongoDBConfigurator;
use MongoDB\Client as MongoDBClient;
use MongoDB\Exception\Exception as MongoDBException;
use Psr\Log\LoggerInterface;

/**
 * Implentation of an EntityProjector using MongoDB: projects the entity reading from the event storage in MongoDB
 *
 * @package Davamigo\Infrastructure\Core\EntityProjector
 * @author davamigo@gmail.com
 */
class MongoDBEntityProjector implements EntityProjector
{
    /**
     * @var MongoDBClient
     */
    protected $client = null;

    /**
     * @var MongoDBConfigurator
     */
    protected $config = null;

    /**
     * Supported events list. $events = [ 'event_name' => 'event_class_name' ]
     *
     * @var array
     */
    protected $events = [];

    /**
     * The monolog object to log events
     *
     * @var LoggerInterface
     */
    protected $logger = null;

    /**
     * MongoDBEntityProjector constructor.
     *
     * @param MongoDBClient $client The mongoDB client
     * @param MongoDBConfigurator $config Configuration object
     * @param EventCollection|Event[]|iterable $events List of supported events
     * @param LoggerInterface $logger Monolog object
     * @throws EntityProjectorErrorException
     */
    public function __construct(
        MongoDBClient $client,
        MongoDBConfigurator $config,
        $events,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->config = $config;
        $this->events = [];
        $this->logger = $logger;
        $this->addSupportedEvents($events);
    }

    /**
     * Finds an entity reading from the event storage
     *
     * @param Uuid $uuid
     * @param array $options
     * @return Entity
     * @throws EntityProjectorNotFoundException
     * @throws EntityProjectorErrorException
     */
    public function findEntity(Uuid $uuid, array $options = []): Entity
    {
        $this->logger->info('EntityProjector::findEntity("' . $uuid->toString() . '"")');

        try {
            $database = $this->client->selectDatabase($this->config->getDefaultDatabase());
            $collection = $database->selectCollection($this->config->getDefaultCollection());

            $cursor = $collection->find([
                'type' => 'event',
                'payload.uuid' => $uuid->toString()
            ], [
                'sort' => ['createdAt' => 1]
            ]);
        } catch (MongoDBException $exc) {
            $this->logger->error('EntityProjector exception: ' . get_class($exc));
            throw new EntityProjectorErrorException('EntityProjector: MongoDB error executing the query!', 0, $exc);
        }

        $resultList = MongoDBConfigurator::cursorToArray($cursor);
        if (empty($resultList)) {
            $this->logger->warning('EntityProjector error: No entity found!');
            throw new EntityProjectorNotFoundException('EntityProjector error: No entity found!');
        }

        $eventData = [];
        foreach ($resultList as $resultItem) {
            $eventData = array_replace_recursive($eventData, $resultItem);
        }

        $this->logger->debug('EntityProjector: Events received and processed.', $eventData);

        $eventName = $eventData['name'] ?? null;
        if (!$eventName) {
            $this->logger->error('EntityProjector error: invalid event format!');
            throw new EntityProjectorErrorException('EntityProjector error: invalid event format!');
        }

        $eventClass = $this->events[$eventName] ?? null;
        if (!$eventClass) {
            $this->logger->error('EntityProjector error: Unknown event!');
            throw new EntityProjectorErrorException('EntityProjector error: Unknown event!');
        }

        $metadata = $eventData['metadata'] ?? [];
        unset($eventData['metadata']);
        unset($eventData['_id']);

        /** @var Event $event */
        $event = call_user_func($eventClass . '::create', $eventData);
        if ($event instanceof EventBase) {
            $event->addMetadata($metadata);
        }

        /** @var Entity $entity */
        $entity = $event->payload();

        $this->logger->debug('EntityProjector: Entity returned.', (array) $entity);

        return $entity;
    }

    /**
     * Adds new events to the supported events list
     *
     * The $event parameter can be:
     * $events = [ Event ]
     * $events = [ 'event_class_name' ]
     * $events = [ 'event_name' => Event ]
     * $events = [ 'event_name' => 'event_clas_name' ]
     *
     * @param EventCollection|Event[]|iterable $events List of supported events
     * @return void
     * @throws EntityProjectorErrorException
     */
    public function addSupportedEvents($events) : void
    {
        foreach ($events as $name => $event) {
            $eventName = null;
            $eventClass = null;

            if (is_string($event) && class_exists($event) && is_subclass_of($event, Event::class)) {
                $eventClass = $event;
            } elseif (is_object($event) && is_subclass_of($event, Event::class)) {
                $eventClass = get_class($event);
            } else {
                $type = is_object($event) ? get_class($event) : gettype($event);
                throw new EntityProjectorErrorException(
                    'MongoDB entity projector error: ' . $type . ' is not a valid event class!'
                );
            }

            if (!is_numeric($name)) {
                $eventName = $name;
            } else {
                $eventName = $eventClass;
            }

            $this->events[$eventName] = $eventClass;
        }
    }
}
