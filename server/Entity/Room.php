<?php

namespace SmartHome\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Selectable;
use JsonSerializable;
use SmartHome\Common\Abstracts\Entity;

/**
 * @Entity @Table(name="rooms")
 * */
class Room extends Entity implements JsonSerializable {

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
     * @var ArrayCollection|Device[]
     *
     * @OneToMany(targetEntity="Device", mappedBy="_room")
     */
    private $_devices;

    /**
     * @var ArrayCollection|Group[]
     *
     * @ManyToMany(targetEntity="Group", mappedBy="_rooms")
     * @JoinTable(name="groups_rooms")
     */
    private $_groups;

    public function __construct () {
        $this->_devices = new ArrayCollection();
        $this->_groups = new ArrayCollection();
    }

    public function jsonSerialize () {
        return [
            'id' => $this->id,
            'name' => $this->name,
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
    }

    public function getDevices () {
        return $this->_devices;
    }

    public function getGroups () {
        return $this->_groups;
    }

    public function getCollection (string $entityName): Selectable {
        $this->checkEntityName($entityName);
        switch ($entityName) {
            case Device::class:
                return $this->_devices;
            case Group::class:
                return $this->_groups;
            default :
                throw new Exception('Device doesn\'t have relation to '.$entityName);
        }
    }

    public function setRelation (string $entityName, ?Entity $value): self {
        throw new Exception('Room doesn\'t have any "many to one" relation!');
    }

}
