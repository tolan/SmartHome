<?php

namespace SmartHome\Entity;

use JsonSerializable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Selectable;
use SmartHome\Common\Abstracts\Entity;

/**
 * @Entity @Table(name="modules")
 * */
class Module extends Entity implements JsonSerializable {

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
    protected $name;

    /**
     *
     * @Column(type="array")
     *
     * @var string
     */
    protected $settingsData;

    /**
     * @var ArrayCollection|Control[]
     *
     * @OneToMany(targetEntity="Control", mappedBy="_module")
     */
    private $_controls;

    /**
     *
     * @var Device
     *
     * @ManyToOne(targetEntity="Device", inversedBy="_modules")
     * @JoinColumn(name="device_id", referencedColumnName="id")
     */
    private $_device;

    public function __construct () {
        $this->_controls = new ArrayCollection();
    }

    public function jsonSerialize () {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'settingsData' => $this->settingsData,
        ];
    }

    public function getId () {
        return $this->id;
    }

    public function getName () {
        return $this->name;
    }

    public function setName ($name) {
        $this->name = $name;
        return $this;
    }

    public function getSettingsData () {
        return $this->settingsData;
    }

    public function setSettingsData ($data) {
        $this->settingsData = $data;
        return $this;
    }

    public function getDevice () {
        return $this->_device;
    }

    public function setDevice ($device) {
        $this->_device = $device;
        return $this;
    }

    public function getControls () {
        return $this->_controls;
    }

    public function getCollection (string $entityName): Selectable {
        $this->checkEntityName($entityName);
        switch ($entityName) {
            case Control::class:
                return $this->_controls;
            default :
                throw new Exception('Device doesn\'t have relation to '.$entityName);
        }
    }

    public function setRelation (string $entityName, ?Entity $value): self {
        $this->checkEntityName($entityName);
        switch ($entityName) {
            case Device::class:
                $this->_device = $value;
                break;
            default :
                throw new Exception('Module doesn\'t have relation to '.$entityName);
        }

        return $this;
    }

}
