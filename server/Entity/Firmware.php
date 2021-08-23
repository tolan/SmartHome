<?php

namespace SmartHome\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Selectable;
use JsonSerializable;
use SmartHome\Common\Abstracts\Entity;

/**
 * This file defines class for firmware entity.
 *
 * @Entity @Table(name="firmwares")
 */
class Firmware extends Entity implements JsonSerializable {

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
     * Filename
     *
     * @var string
     *
     * @Column(type="string")
     */
    protected $filename;

    /**
     * Devices
     *
     * @var ArrayCollection|Device[]
     *
     * @OneToMany(targetEntity="Device", mappedBy="_firmware")
     */
    private $_devices;

    /**
     * Contruct method
     */
    public function __construct() {
        $this->_devices = new ArrayCollection();
    }

    /**
     * Returns data for JSON serialize.
     *
     * @return array
     */
    public function jsonSerialize() {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'filename' => $this->filename,
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
     * Gets filename
     *
     * @return string
     */
    public function getFilename() {
        return $this->filename;
    }

    /**
     * Sets filename
     *
     * @param string $filename Filename
     *
     * @return $this
     */
    public function setFilename($filename) {
        $this->filename = $filename;
        return $this;
    }

    /**
     * Gets devices
     *
     * @return ArrayCollection
     */
    public function getDevices() {
        return $this->_devices;
    }

    /**
     * Gets path dir to firmwares
     *
     * @return string
     */
    public function getDir(): string {
        $dir = __DIR__.'/../../firmwares';
        return $dir.'/'.$this->getId();
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
            case Device::class:
                return $this->_devices;
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
        throw new Exception('Firmware doesn\'t have any "many to one" relation!');
    }

}
