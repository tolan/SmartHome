<?php

namespace SmartHome\Entity;

use JsonSerializable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Selectable;
use SmartHome\Common\Abstracts\Entity;

/**
 * This file defines class for module entity.
 *
 * @Entity @Table(name="modules")
 */
class Module extends Entity implements JsonSerializable {

    /**
     * Id
     *
     * @var integer
     *
     * @Id @Column(type="integer") @GeneratedValue
     */
    protected $id;

    /**
     * Name
     *
     * @var string
     *
     * @Column(type="string")
     */
    protected $name;

    /**
     * Settings data
     *
     * @var string
     *
     * @Column(type="array")
     */
    protected $settingsData;

    /**
     * Controls
     *
     * @var ArrayCollection|Control[]
     *
     * @OneToMany(targetEntity="Control", mappedBy="_module")
     */
    private $_controls;

    /**
     * Device
     *
     * @var Device
     *
     * @ManyToOne(targetEntity="Device", inversedBy="_modules")
     * @JoinColumn(name="device_id",     referencedColumnName="id")
     */
    private $_device;

    /**
     * Contruct method
     */
    public function __construct() {
        $this->_controls = new ArrayCollection();
    }

    /**
     * Returns serialized data from JSON serialize.
     *
     * @return array
     */
    public function jsonSerialize() {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'settingsData' => $this->settingsData,
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
     * Gets name
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Sets name
     *
     * @param string $name Name
     *
     * @return $this
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * Gets settings data
     *
     * @return array
     */
    public function getSettingsData() {
        return $this->settingsData;
    }

    /**
     * Sets settings data
     *
     * @param array $data Settings data
     *
     * @return $this
     */
    public function setSettingsData($data) {
        $this->settingsData = $data;
        return $this;
    }

    /**
     * Gets Device
     *
     * @return Devie
     */
    public function getDevice() {
        return $this->_device;
    }

    /**
     * Sets Device
     *
     * @param Device $device Device
     *
     * @return $this
     */
    public function setDevice($device) {
        $this->_device = $device;
        return $this;
    }

    /**
     * Gets controls
     *
     * @return ArrayCollection
     */
    public function getControls() {
        return $this->_controls;
    }

    /**
     * Gets collection by entity name
     *
     * @param string $entityName Entity name
     *
     * @return Selectable
     *
     * @throws Exception
     */
    public function getCollection(string $entityName): Selectable {
        $this->checkEntityName($entityName);
        switch ($entityName) {
            case Control::class:
                return $this->_controls;
            default:
                throw new Exception('Device doesn\'t have relation to '.$entityName);
        }
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
            case Device::class:
                $this->_device = $value;
                break;
            default:
                throw new Exception('Module doesn\'t have relation to '.$entityName);
        }

        return $this;
    }

}
