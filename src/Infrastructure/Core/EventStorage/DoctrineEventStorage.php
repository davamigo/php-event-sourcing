<?php

namespace Davamigo\Infrastructure\Core\EventStorage;

use Davamigo\Domain\Core\Entity\Entity;
use Davamigo\Domain\Core\Event\Event;
use Davamigo\Domain\Core\EventStorage\EventStorage;
use Davamigo\Domain\Core\EventStorage\EventStorageException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Psr\Log\LoggerInterface;

/**
 * Event storage implementation using Doctrine
 *
 * @package Davamigo\Infrastructure\Core\EventStorage
 */
class DoctrineEventStorage implements EventStorage
{
    /**
     * The EntityManager is the central access point to Doctrine ORM functionality
     *
     * @var EntityManagerInterface
     */
    protected $manager = null;

    /**
     * The monolog object to log events
     *
     * @var LoggerInterface
     */
    protected $logger = null;

    /**
     * MongoDBEventStorer constructor.
     *
     * @param EntityManagerInterface $manager The entity manager from Doctrine
     * @param LoggerInterface        $logger  Monolog object
     */
    public function __construct(
        EntityManagerInterface $manager,
        LoggerInterface $logger
    ) {
        $this->manager = $manager;
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
        // Extract the payload from the event
        $payload = $event->payload();
        if (!$payload instanceof Entity) {
            throw new EventStorageException('The payload of the event has to be an Entity!');
        }

        /** @var Entity $entity */
        $entity = $payload;

        // Get the entity name
        $fullEntityName = get_class($entity);
        $entityNameComponents = explode('\\', $fullEntityName);
        $entityName = end($entityNameComponents);

        // Get the Doctrine entity name
        $ormFullEntityName = $this->findOrmEntityName($entityName);

        try {
            switch ($event->action()) {
                case Event::ACTION_INSERT:
                    $ormEntity = new $ormFullEntityName();
                    $ormEntity->fromDomainEntity($entity);
                    $this->manager->persist($ormEntity);
                    break;

                case Event::ACTION_UPDATE:
                    $ormEntity = $this->manager->find($ormFullEntityName, $entity->uuid()->toString());
                    $ormEntity->fromDomainEntity($entity);
                    $this->manager->persist($ormEntity);
                    break;

                case Event::ACTION_DELETE:
                    $ormEntity = $this->manager->find($ormFullEntityName, $entity->uuid());
                    $this->manager->remove($ormEntity);
                    break;

                default:
                    throw new EventStorageException('Action ' . $event->action() . ' nor supported!');
            }

            $this->manager->flush();
        } catch (ORMException $exc) {
            throw new EventStorageException('An error occurred in Doctrine while processing an event', 0, $exc);
        } catch (ORMInvalidArgumentException $exc) {
            throw new EventStorageException('An error occurred in Doctrine while processing an event', 0, $exc);
        }

        return $this;
    }

    /**
     * Find the equivalent entity inside the Doctrine entity manager
     *
     * @param string $entityName
     * @return string
     * @throws EventStorageException
     */
    protected function findOrmEntityName(string $entityName) : string
    {
        try {
            $ormEntities = $this->manager->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
        } catch (ORMException $exc) {
            throw new EventStorageException('Error retrieving the Doctrine ORM entities!');
        }

        foreach ($ormEntities as $ormFullEntityName) {
            $ormEntityMethods = get_class_methods($ormFullEntityName);
            if (in_array('fromDomainEntity', $ormEntityMethods)) {
                $entityNameComponents = explode('\\', $ormFullEntityName);
                $ormEntityName = end($entityNameComponents);
                if ($entityName == $ormEntityName) {
                    return $ormFullEntityName;
                }
            }
        }

        throw new EventStorageException('Entity ' . $entityName . ' not found as Doctrine entity!');
    }
}
