<?php

namespace SmartHome\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Selectable;
use JsonSerializable;
use SmartHome\Common\Abstracts\Entity;

/**
 * This file defines class for room entity.
 *
 * @Entity @Table(name="rooms")
 */
class Room extends Entity implements JsonSerializable {

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
     * Devices
     *
     * @var ArrayCollection|Device[]
     *
     * @OneToMany(targetEntity="Device", mappedBy="_room")
     */
    private $_devices;

    /**
     * Groups
     *
     * @var ArrayCollection|Group[]
     *
     * @ManyToMany(targetEntity="Group", mappedBy="_rooms")
     * @JoinTable(name="groups_rooms")
     */
    private $_groups;

    /**
     * Contruct method
     */
    public function __construct() {
        $this->_devices = new ArrayCollection();
        $this->_groups  = new ArrayCollection();
    }

    /**
     * Returns data for JSON serialize.
     *
     * @return array
     */
    public function jsonSerialize() {
        return [
            'id'   => $this->id,
            'name' => $this->name,
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
     * Gets devices
     *
     * @return ArrayCollection
     */
    public function getDevices() {
        return $this->_devices;
    }

    /**
     * Gets groups
     *
     * @return ArrayCollection
     */
    public function getGroups() {
        return $this->_groups;
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
            case Group::class:
                return $this->_groups;
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
        throw new Exception('Room doesn\'t have any "many to one" relation!');
    }

}
