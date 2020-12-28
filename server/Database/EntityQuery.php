<?php

namespace SmartHome\Database;

use Doctrine\ORM\QueryBuilder;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class EntityQuery extends RelationQuery {

    public function build (QueryBuilder $dql): QueryBuilder {
        $dql->select($this->getAlias())
                ->from($this->getEntityName(), $this->getAlias());

        foreach ($this->getRelations() as $relation) {
            $relation->build($dql);
        }

        $this->buildConditions($dql);

        return $dql;
    }

    public static function create (string $targetEntity, array $relations = [], array $conditions = []): EntityQuery {
        $query = new EntityQuery($targetEntity);

        foreach ($relations as $set) {
            $rel = $query;
            foreach ($set as $relation) {
                $newRel = new RelationQuery($relation);
                $rel->with($newRel);
                $rel = $newRel;
            }
        }

        $query->conditions($conditions);

        return $query;
    }

}
