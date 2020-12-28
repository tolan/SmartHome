<?php

namespace SmartHome\Database\Abstracts;

use SmartHome\Database\Exception;
use SmartHome\Common\Abstracts\Entity;
use Doctrine\ORM\QueryBuilder;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
abstract class Query {

    /**
     *
     * @var string
     */
    private $_alias;

    /**
     *
     * @var string
     */
    private $_entityName;

    /**
     *
     * @var Query
     */
    private $_parent;

    /**
     *
     * @var Array
     */
    private $_conditions = [];

    /**
     *
     * @var int
     */
    private static $alias = 1;

    public function __construct (string $entityName) {
        $this->checkEntityClass($entityName);

        $this->_alias = 't'.self::$alias++;
        $this->_entityName = $entityName;
    }

    public function getEntityName (): string {
        return $this->_entityName;
    }

    protected function getAlias (): string {
        return $this->_alias;
    }

    public function conditions (array $params): self {
        $this->_conditions = $params;

        return $this;
    }

    protected function getConditions (): array {
        return $this->_conditions;
    }

    protected function checkEntityClass (string $entityName): self {
        if (!is_subclass_of($entityName, Entity::class)) {
            throw new Exception('Given entity '.$entityName.' is not a subclass of '.Entity::class);
        }

        return $this;
    }

    protected function setParent (Query $query): self {
        $this->_parent = $query;

        return $query;
    }

    protected function getParent (): Query {
        return $this->_parent;
    }

    protected function buildConditions (QueryBuilder $dql): QueryBuilder {
        foreach ($this->_conditions as $key => $value) {
            $id = uniqid('p');
            if (is_array($value)) {
                $dql->andWhere($this->getAlias().'.'.$key.' IN (:'.$id.')');
            } elseif (is_scalar($value)) {
                $dql->andWhere($this->getAlias().'.'.$key.' = :'.$id);
            }

            $dql->setParameter($id, $value);
        }

        return $dql;
    }

    abstract public function build (QueryBuilder $dql): QueryBuilder;
}
