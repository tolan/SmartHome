<?php

namespace SmartHome\Database\Abstracts;

use SmartHome\Database\Exception;
use SmartHome\Common\Abstracts\Entity;
use Doctrine\ORM\QueryBuilder;

/**
 * This file defines abstract class for build entity query
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
abstract class Query {

    /**
     * Alias name
     *
     * @var string
     */
    private $_alias;

    /**
     * Entity class name
     *
     * @var string
     */
    private $_entityName;

    /**
     * Parent entiy class name
     *
     * @var Query
     */
    private $_parent;

    /**
     * Set of conditions
     *
     * @var array
     */
    private $_conditions = [];

    /**
     * Alias iterator
     *
     * @var integer
     */
    private static $_aliasCounter = 1;

    /**
     * Construct method for inject dependencies
     *
     * @param string $entityName Entity class name
     */
    public function __construct(string $entityName) {
        $this->checkEntityClass($entityName);

        $this->_alias      = 't'.self::$_aliasCounter++;
        $this->_entityName = $entityName;
    }

    /**
     * Returns entity class name
     *
     * @return string
     */
    public function getEntityName(): string {
        return $this->_entityName;
    }

    /**
     * Returns alias name
     *
     * @return string
     */
    protected function getAlias(): string {
        return $this->_alias;
    }

    /**
     * Sets array of conditions
     *
     * @param array $conditions Conditions
     *
     * @return self
     */
    public function conditions(array $conditions): self {
        $this->_conditions = $conditions;

        return $this;
    }

    /**
     * Returns set of conditions
     *
     * @return array
     */
    protected function getConditions(): array {
        return $this->_conditions;
    }

    /**
     * Checks that given entity name is subclass of Entity.
     *
     * @param string $entityName Entity class name
     *
     * @return self
     *
     * @throws Exception
     */
    protected function checkEntityClass(string $entityName): self {
        if (!is_subclass_of($entityName, Entity::class)) {
            throw new Exception('Given entity '.$entityName.' is not a subclass of '.Entity::class);
        }

        return $this;
    }

    /**
     * Sets parent query
     *
     * @param Query $query Query
     *
     * @return self
     */
    protected function setParent(Query $query): self {
        $this->_parent = $query;

        return $query;
    }

    /**
     * Returns parent query
     *
     * @return Query
     */
    protected function getParent(): Query {
        return $this->_parent;
    }

    /**
     * Builds conditions for given dql.
     *
     * @param QueryBuilder $dql Query builder
     *
     * @return QueryBuilder
     */
    protected function buildConditions(QueryBuilder $dql): QueryBuilder {
        foreach ($this->_conditions as $key => $value) {
            $id = uniqid('p');
            if (is_array($value)) {
                $dql->andWhere($this->getAlias().'.'.$key.' IN (:'.$id.')');
            } else if (is_scalar($value)) {
                $dql->andWhere($this->getAlias().'.'.$key.' = :'.$id);
            }

            $dql->setParameter($id, $value);
        }

        return $dql;
    }

    /**
     * Build query
     *
     * @param QueryBuild $dql Query builder
     *
     * @return QueryBuilder
     */
    abstract public function build(QueryBuilder $dql): QueryBuilder;

}
