<?php

namespace SmartHome\Database;

use Doctrine\ORM\QueryBuilder;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class RelationQuery extends Abstracts\Query {

    /**
     *
     * @var RelationQuery[]
     */
    private $_relations = [];

    public function with (RelationQuery $query): self {
        $this->_relations[] = $query;

        $query->setParent($this);
        return $this;
    }

    public function build (QueryBuilder $dql): QueryBuilder {
        $dql->select(join(', ', array_merge($dql->getAllAliases(), [$this->getAlias()])));

        $matchStrings = ['mappedBy', 'inversedBy'];
        $matchedBy = null;
        foreach ($matchStrings as $match) {
            if (($matchedBy = $this->getEntityName()::findMappedBy($this->getParent()->getEntityName(), $match))) {
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
     *
     * @return RelationQuery[]
     */
    protected function getRelations (): array {
        return $this->_relations;
    }

}
