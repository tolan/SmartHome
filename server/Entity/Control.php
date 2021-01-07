<?php

namespace SmartHome\Entity;

use JsonSerializable;
use SmartHome\Common\Abstracts\Entity;
use SmartHome\Common\Exception;
use Doctrine\Common\Collections\Selectable;

/**
 * This file defines class for control entity.
 *
 * @Entity @Table(name="controls")
 * */
class Control extends Entity implements JsonSerializable {

    /**
     * Id
     *
     * @var integer
     *
     * @Id @Column(type="integer") @GeneratedValue
     */
    protected $id;

    /**
     * Type
     *
     * @var string
     *
     * @Column(type="string")
     */
    protected $type;

    /**
     * Control data
     *
     * @var array
     *
     * @Column(type="array")
     */
    protected $controlData;

    /**
     * Module
     *
     * @var Module
     *
     * @ManyToOne(targetEntity="Module", inversedBy="_controls")
     * @JoinColumn(name="module_id",     referencedColumnName="id")
     */
    private $_module;

    /**
     * Returns serialized data from JSON serialize.
     *
     * @return array
     */
    public function jsonSerialize() {
        return [
            'id'          => $this->id,
            'type'        => $this->type,
            'controlData' => $this->controlData,
        ];
    }

    /**
     * Gets Id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Gets type
     *
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Sets type
     *
     * @param string $type Type
     *
     * @return $this
     */
    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    /**
     * Gets control data
     *
     * @return array
     */
    public function getControlData() {
        return $this->controlData;
    }

    /**
     * Sets control data
     *
     * @param array $data Data
     *
     * @return $this
     */
    public function setControlData($data) {
        $this->controlData = $data;
        return $this;
    }

    /**
     * Gets module
     *
     * @return Module
     */
    public function getModule() {
        return $this->_module;
    }

    /**
     * Sets module
     *
     * @param Module $module Module
     *
     * @return $this
     */
    public function setModule($module) {
        $this->_module = $module;
        return $this;
    }

    /**
     * Gets collection by entity name
     *
     * @param string $entityName Entity name
     *
     * @return void
     *
     * @throws Exception
     */
    public function getCollection(string $entityName): Selectable {
        throw new Exception('Control doesn\'t have any "many to many" relation!');
    }

    /**
     * Sets relation entity
     *
     * @param string      $entityName Entity name
     * @param Entity|null $value      Entity
     *
     * @return self
     *
     * @throws Exception
     */
    public function setRelation(string $entityName, ?Entity $value): self {
        $this->checkEntityName($entityName);
        switch ($entityName) {
            case Module::class:
                $this->_module = $value;
                break;
            default:
                throw new Exception('Control doesn\'t have relation to '.$entityName);
        }

        return $this;
    }

}
