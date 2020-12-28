<?php

namespace SmartHome\Entity;

use JsonSerializable;
use SmartHome\Common\Abstracts\Entity;
use SmartHome\Common\Exception;
use Doctrine\Common\Collections\Selectable;

/**
 * @Entity @Table(name="controls")
 * */
class Control extends Entity implements JsonSerializable {

    /**
     * @Id @Column(type="integer") @GeneratedValue
     *
     * @var int
     */
    protected $id;

    /**
     * @Column(type="string")
     *
     * @var string
     */
    protected $type;

    /**
     *
     * @Column(type="array")
     *
     * @var string
     */
    protected $controlData;

    /**
     *
     * @var Module
     *
     * @ManyToOne(targetEntity="Module", inversedBy="_controls")
     * @JoinColumn(name="module_id", referencedColumnName="id")
     */
    private $_module;

    public function jsonSerialize () {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'controlData' => $this->controlData,
        ];
    }

    public function getId () {
        return $this->id;
    }

    public function getType () {
        return $this->type;
    }

    public function setType ($type) {
        $this->type = $type;
        return $this;
    }

    public function getControlData () {
        return $this->controlData;
    }

    public function setControlData ($data) {
        $this->controlData = $data;
        return $this;
    }

    public function getModule () {
        return $this->_module;
    }

    public function setModule ($module) {
        $this->_module = $module;
        return $this;
    }

    public function getCollection (string $entityName): Selectable {
        throw new Exception('Control doesn\'t have any "many to many" relation!');
    }

    public function setRelation (string $entityName, ?Entity $value): self {
        $this->checkEntityName($entityName);
        switch ($entityName) {
            case Module::class:
                $this->_module = $value;
                break;
            default :
                throw new Exception('Control doesn\'t have relation to '.$entityName);
        }

        return $this;
    }

}
