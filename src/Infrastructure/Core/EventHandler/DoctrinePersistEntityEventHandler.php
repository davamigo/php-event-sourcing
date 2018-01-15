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
        $this->logger->info('DoctrinePersistEntityEventHandler: Event received.', [ 'event' => $event ]);

        // Extract the payload from the event
        $payload = $event->payload();
        if (!$payload instanceof Entity) {
            throw new EventHandlerException(
                'DoctrinePersistEntityEventHandler error: The payload of the event has to be an Entity!'
            );
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
                    call_user_func([$ormEntity, 'fromDomainEntity'], $entity);
                    $this->manager->persist($ormEntity);
                    break;

                case Event::ACTION_UPDATE:
                    $ormEntity = $this->manager->getRepository($ormFullEntityName)->find($entity->uuid()->toString());
                    call_user_func([$ormEntity, 'fromDomainEntity'], $entity);
                    $this->manager->persist($ormEntity);
                    break;

                case Event::ACTION_DELETE:
                    $ormEntity = $this->manager->getRepository($ormFullEntityName)->find($entity->uuid()->toString());
                    $this->manager->remove($ormEntity);
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
     * Find the equivalent entity inside the Doctrine entity manager
     *
     * @param string $entityName
     * @return string
     * @throws EventHandlerException
     */
    protected function findOrmEntityName(string $entityName) : string
    {
        try {
            $ormEntities = $this->manager->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
        } catch (ORMException $exc) {
            $error = 'DoctrinePersistEntityEventHandler - Error retrieving the Doctrine ORM entities';
            $this->logger->error($error . ' - ' . $exc->getMessage());
            $this->logger->debug($exc);
            throw new EventHandlerException($error, 0, $exc);
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

        $error = 'DoctrinePersistEntityEventHandler error - Entity ' . $entityName . ' not found as Doctrine entity';
        $this->logger->error($error);

        throw new EventHandlerException($error);
    }
}
