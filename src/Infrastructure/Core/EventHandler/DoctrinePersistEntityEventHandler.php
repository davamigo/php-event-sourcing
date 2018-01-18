<?php

namespace Davamigo\Infrastructure\Core\EventHandler;

use Davamigo\Domain\Core\Entity\Entity;
use Davamigo\Domain\Core\Event\Event;
use Davamigo\Domain\Core\EventHandler\EventHandler;
use Davamigo\Domain\Core\EventHandler\EventHandlerException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Psr\Log\LoggerInterface;

/**
 * Event handler implementation ro persist the event in a relational database using Doctrine
 *
 * @package Davamigo\Infrastructure\Core\EventHandler
 * @author davamigo@gmail.com
 */
class DoctrinePersistEntityEventHandler implements EventHandler
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
     * DoctrinePersistEntityEventHandler constructor.
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
     * Processes the event to persist the payload using Doctrine
     *
     * @param Event $event
     * @return EventHandler
     * @throws EventHandlerException
     */
    public function handleEvent(Event $event): EventHandler
    {
        $this->logger->info('DoctrinePersistEntityEventHandler: Handling event.', [ 'event' => $event ]);

        // Extract the payload from the event
        $payload = $event->payload();
        if (!$payload instanceof Entity) {
            throw new EventHandlerException(
                'DoctrinePersistEntityEventHandler error: The payload of the event has to be an Entity!'
            );
        }

        /** @var Entity $entity */
        $entity = $payload;

        try {
            switch ($event->action()) {
                case Event::ACTION_INSERT:
                    $this->insert($entity);
                    break;

                case Event::ACTION_UPDATE:
                    $this->update($entity);
                    break;

                case Event::ACTION_DELETE:
                    $this->delete($entity);
                    break;

                default:
                    throw new EventHandlerException('Action ' . $event->action() . ' nor supported!');
            }

            $this->manager->flush();
        } catch (ORMException $exc) {
            $error = 'DoctrinePersistEntityEventHandler - An error occurred in Doctrine while processing an event';
            $this->logger->error($error . ' - ' . $exc->getMessage());
            $this->logger->debug($exc);
            throw new EventHandlerException($error, 0, $exc);
        } catch (ORMInvalidArgumentException $exc) {
            $error = 'DoctrinePersistEntityEventHandler - An error occurred in Doctrine while processing an event';
            $this->logger->error($error . ' - ' . $exc->getMessage());
            $this->logger->debug($exc);
            throw new EventHandlerException($error, 0, $exc);
        }

        return $this;
    }

    /**
     * Inserts the entity
     *
     * @param Entity $entity
     * @return void
     * @throws EventHandlerException
     */
    protected function insert(Entity $entity) : void
    {
        // Get the Doctrine entity class
        $ormEntityClass = $this->findOrmEntityClass($entity);

        // Create the new entity
        $ormEntity = new $ormEntityClass();

        // Persist the entity
        $this->fillOrmEntityFromDomainEntity($ormEntity, $entity);
        $this->manager->persist($ormEntity);
    }

    /**
     * Updates the entity
     *
     * @param Entity $entity
     * @return void
     * @throws EventHandlerException
     */
    protected function update(Entity $entity) : void
    {
        // Get the Doctrine entity class
        $ormEntityClass = $this->findOrmEntityClass($entity);

        // Find the entity
        $ormEntity = $this->manager->getRepository($ormEntityClass)->find($entity->uuid()->toString());

        // Persist the entity
        $this->fillOrmEntityFromDomainEntity($ormEntity, $entity);
        $this->manager->persist($ormEntity);
    }

    /**
     * Deletes the entity
     *
     * @param Entity $entity
     * @return void
     * @throws EventHandlerException
     */
    protected function delete(Entity $entity) : void
    {
        // Get the Doctrine entity class
        $ormEntityClass = $this->findOrmEntityClass($entity);

        // Find the entity
        $ormEntity = $this->manager->getRepository($ormEntityClass)->find($entity->uuid()->toString());

        // Remove the entity
        $this->manager->remove($ormEntity);
    }

    /**
     * Find the equivalent entity inside the Doctrine entity manager
     *
     * @param Entity $entity
     * @return string
     * @throws EventHandlerException
     */
    protected function findOrmEntityClass(Entity $entity) : string
    {
        // Get the entity name
        $fullEntityName = get_class($entity);
        $entityNameComponents = explode('\\', $fullEntityName);
        $entityName = end($entityNameComponents);

        try {
            $ormEntities = $this->manager->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
        } catch (ORMException $exc) {
            $error = 'DoctrinePersistEntityEventHandler - Error retrieving the Doctrine ORM entities';
            $this->logger->error($error . ' - ' . $exc->getMessage());
            $this->logger->debug($exc);
            throw new EventHandlerException($error, 0, $exc);
        }

        foreach ($ormEntities as $ormEntityClass) {
            $ormEntityMethods = get_class_methods($ormEntityClass);
            if (in_array('fromDomainEntity', $ormEntityMethods)) {
                $entityNameComponents = explode('\\', $ormEntityClass);
                $ormEntityName = end($entityNameComponents);
                if ($entityName == $ormEntityName) {
                    return $ormEntityClass;
                }
            }
        }

        $error = 'DoctrinePersistEntityEventHandler error - Entity ' . $entityName . ' not found as Doctrine entity';
        $this->logger->error($error);

        throw new EventHandlerException($error);
    }

    /**
     * Replaces the content of the ORM entity with the domain entity one.
     *
     * @param object $ormEntity
     * @param Entity $domainEntity
     * @return void
     */
    protected function fillOrmEntityFromDomainEntity($ormEntity, Entity $domainEntity) : void
    {
        call_user_func([$ormEntity, 'fromDomainEntity'], $domainEntity);

        $rawData = $domainEntity->serialize();
        foreach (array_keys($rawData) as $rawField) {
            $getFieldMethod = 'get' . $rawField;
            $setFieldMethod = 'set' . $rawField;
            if (method_exists($ormEntity, $getFieldMethod)
                && method_exists($ormEntity, $setFieldMethod)) {
                $ormSubentity = call_user_func([$ormEntity, $getFieldMethod]);
                if (null !== $ormSubentity && method_exists($ormSubentity, 'getUuid')) {
                    $repo = $this->manager->getRepository(get_class($ormSubentity));
                    if (null !== $repo) {
                        $ormSubentity = $repo->find(call_user_func([$ormSubentity, 'getUuid']));
                        call_user_func([$ormEntity, $setFieldMethod], $ormSubentity);
                    }
                }
            }
        }
    }
}
