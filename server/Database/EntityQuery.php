<?php

namespace SmartHome\Database;

use Doctrine\ORM\QueryBuilder;

/**
 * This file defines class for generate query of entities.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class EntityQuery extends RelationQuery {

    /**
     * Builds DQL query
     *
     * @param QueryBuilder $dql Query builder
     *
     * @return QueryBuilder
     */
    public function build(QueryBuilder $dql): QueryBuilder {
        $dql->select($this->getAlias())->from($this->getEntityName(), $this->getAlias());

        foreach ($this->getRelations() as $relation) {
            $relation->build($dql);
        }

        $this->buildConditions($dql);

        return $dql;
    }

    /**
     * Helper method for create simple EntityQuery
     *
     * @param string $targetEntity Target entity class name
     * @param array  $relations    Array of array relations
     * @param array  $conditions   Array of equal conditions
     *
     * @return EntityQuery
     */
    public static function create(string $targetEntity, array $relations = [], array $conditions = []): EntityQuery {
        $query = new EntityQuery($targetEntity);

        foreach ((array)$relations as $set) {
            $rel = $query;
            foreach ((array)$set as $relation) {
                $newRel = new RelationQuery($relation);
                $rel->with($newRel);
                $rel    = $newRel;
            }
        }

        $query->conditions($conditions);

        return $query;
    }

}
