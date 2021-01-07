<?php

namespace SmartHome\Common;

use SmartHome\Common\Abstracts\Entity;
use SmartHome\Database\EntityQuery;
use SmartHome\Event\{
    Abstracts\AMessage,
    Mediator,
    Messages
};
use DI\Container;
use Doctrine\ORM\EntityManager;

/**
 * This file defines class for common service.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
final class Service {

    /**
     * Container
     *
     * @var Container
     */
    private $_container;

    /**
     * Construct method for inject dependencies
     *
     * @param Container $container Container
     */
    public function __construct(Container $container) {
        $this->_container = $container;
    }

    /**
     * Finds entitites by given EntityQuery
     *
     * @param EntityQuery $entityQuery EntityQuery
     *
     * @return Entity[]
     */
    public function find(EntityQuery $entityQuery): array {
        $queryBuilder = $this->_getDb()->createQueryBuilder();
        $dql          = $entityQuery->build($queryBuilder);

        return $dql->getQuery()->getResult();
    }

    /**
     * Finds one entity or null by given EntityQuery
     *
     * @param EntityQuery $entityQuery EntityQuery
     *
     * @return Entity|null
     */
    public function findOne(EntityQuery $entityQuery): ?Entity {
        $queryBuilder = $this->_getDb()->createQueryBuilder();
        $dql          = $entityQuery->build($queryBuilder);

        return $dql->getQuery()->getOneOrNullResult();
    }

    /**
     * Persists entity
     *
     * @param Entity $entity Entity
     * @param bool   $flush  Flush db
     *
     * @return Entity
     */
    public function persist(Entity $entity, bool $flush = false): Entity {
        $this->_getDb()->persist($entity);
        if ($flush) {
            $this->_getDb()->flush();
        }

        $this->_sendMessage(new Messages\Entity\Update($entity));

        return $entity;
    }

    /**
     * Removes entity
     *
     * @param Entity $entity Entity
     * @param bool   $flush  Flush db
     *
     * @return Entity
     */
    public function remove(Entity $entity, bool $flush = false): Entity {
        $this->_getDb()->remove($entity);

        if ($flush) {
            $this->_getDb()->flush();
        }

        $this->_sendMessage(new Messages\Entity\Delete($entity));

        return $entity;
    }

    /**
     * Flushes db
     *
     * @return Service
     */
    public function flush(): Service {
        $this->_getDb()->flush();

        return $this;
    }

    /**
     * Clear up db
     *
     * @return Service
     */
    public function clear(): Service {
        $this->_getDb()->clear();

        return $this;
    }

    /**
     * Assembles relation many to many relation between one entity and their related entities
     *
     * @param Entity $to         Target entity
     * @param string $whatEntity Entity name of related entities
     * @param array  $what       Set of related entities
     * @param bool   $flush      Flush db
     *
     * @return Service
     */
    public function assembleRelationsManyToMany(Entity $to, string $whatEntity, array $what = [], bool $flush = false): Service {
        $this->_checkWhatEntities($whatEntity, $what);
        $toEntity = get_class($to);

        array_map(function(Entity $entity) use ($to, $toEntity) {
            $entity->getCollection($toEntity)->removeElement($to);
            $this->persist($entity);
        }, $to->getCollection($whatEntity)->toArray());

        $to->getCollection($whatEntity)->clear();

        array_map(function(Entity $entity) use ($to, $toEntity, $whatEntity) {
            $to->getCollection($whatEntity)->add($entity);
            $entity->getCollection($toEntity)->add($to);
            $this->persist($entity);
        }, $what);

        $this->persist($to, $flush);
        return $this;
    }

    /**
     * Assembles relation many to one relation between one entity and their related entities
     *
     * @param Entity $to         Target entity
     * @param string $whatEntity Entity name of related entities
     * @param array  $what       Set of related entities
     * @param bool   $flush      Flush db
     *
     * @return Service
     */
    public function assembleRelationsManyToOne(Entity $to, string $whatEntity, array $what = [], bool $flush = false): Service {
        $this->_checkWhatEntities($whatEntity, $what);
        $toEntity = get_class($to);

        if (!empty($what)) {
            array_map(function(Entity $entity) use ($to, $whatEntity, $toEntity) {
                $to->getCollection($whatEntity)->add($entity);
                $entity->setRelation($toEntity, $to);
                $this->persist($entity);
            }, $what);
        } else {
            array_map(function(Entity $entity) use ($toEntity) {
                $entity->setRelation($toEntity, null);
                $this->persist($entity);
            }, $to->getCollection($whatEntity)->toArray());

            $to->getCollection($whatEntity)->clear();
        }

        $this->persist($to, $flush);
        return $this;
    }

    /**
     * Sends message to mediator
     *
     * @param AMessage $message Message
     *
     * @return void
     */
    private function _sendMessage(AMessage $message) {
        $this->_container->get(Mediator::class)->send($message);
    }

    /**
     * Gets db instance
     *
     * @return EntityManager
     */
    private function _getDb(): EntityManager {
        return $this->_container->get('db');
    }

    /**
     * Chcecks that all target entities ($what) are instance of taget entity ($whatEntity)
     *
     * @param string $whatEntity Target entity name
     * @param array  $what       Set of target entitites
     *
     * @return Service
     */
    private function _checkWhatEntities(string $whatEntity, array $what = []): Service {
        array_map(function(Entity $entity) use ($whatEntity) {
            if (!is_a($entity, $whatEntity)) {
                throw new Exception('All "$what" items must be the same class.');
            }
        }, $what);

        return $this;
    }

}
