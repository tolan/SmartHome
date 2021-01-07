<?php

namespace SmartHome\Database;

use Doctrine\ORM\QueryBuilder;

/**
 * This file defines class for build relation query.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class RelationQuery extends Abstracts\Query {

    /**
     * Sets of RelationsQuery
     *
     * @var RelationQuery[]
     */
    private $_relations = [];

    /**
     * Assigns RelationQuery
     *
     * @param RelationQuery $query Relation query
     *
     * @return self
     */
    public function with(RelationQuery $query): self {
        $this->_relations[] = $query;

        $query->setParent($this);
        return $this;
    }

    /**
     * Builds relations to query
     *
     * @param QueryBuilder $dql Query builder
     *
     * @return QueryBuilder
     */
    public function build(QueryBuilder $dql): QueryBuilder {
        $dql->select(join(', ', array_merge($dql->getAllAliases(), [$this->getAlias()])));

        $matchStrings = ['mappedBy', 'inversedBy'];
        $matchedBy    = null;
        foreach ($matchStrings as $match) {
            $matchedBy = $this->getEntityName()::findMappedBy($this->getParent()->getEntityName(), $match);
            if ($matchedBy) {
                break;
            }
        }

        $dql->leftJoin($this->getParent()->getAlias().'.'.$matchedBy, $this->getAlias());

        foreach ($this->getRelations() as $relation) {
            $relation->build($dql);
        }

        $this->buildConditions($dql);

        return $dql;
    }

    /**
     * Returns relations
     *
     * @return RelationQuery[]
     */
    protected function getRelations(): array {
        return $this->_relations;
    }

}
