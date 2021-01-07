<?php

namespace SmartHome\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Selectable;
use JsonSerializable;
use SmartHome\Common\Abstracts\Entity;

/**
 * This file defines class for group entity.
 *
 * @Entity @Table(name="groups")
 */
class Group extends Entity implements JsonSerializable {

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
     * Users
     *
     * @var ArrayCollection|User[]
     *
     * @ManyToMany(targetEntity="User", mappedBy="_groups")
     * @JoinTable(name="users_groups")
     */
    private $_users;

    /**
     * Permissions
     *
     * @var ArrayCollection|Permission[]
     *
     * @ManyToMany(targetEntity="Permission", inversedBy="_groups")
     * @JoinTable(name="groups_permissions")
     */
    private $_permissions;

    /**
     * Rooms
     *
     * @var ArrayCollection|Room[]
     *
     * @ManyToMany(targetEntity="Room", inversedBy="_groups")
     * @JoinTable(name="groups_rooms")
     */
    private $_rooms;

    /**
     * Contruct method
     */
    public function __construct() {
        $this->_users       = new ArrayCollection();
        $this->_permissions = new ArrayCollection();
        $this->_rooms       = new ArrayCollection();
    }

    /**
     * Returns serialized data from JSON serialize.
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
     * Gets users
     *
     * @return ArrayCollection
     */
    public function getUsers() {
        return $this->_users;
    }

    /**
     * Gets permissions
     *
     * @return ArrayCollection
     */
    public function getPermissions() {
        return $this->_permissions;
    }

    /**
     * Gets rooms
     *
     * @return ArrayCollection
     */
    public function getRooms() {
        return $this->_rooms;
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
            case User::class:
                return $this->_users;
            case Permission::class:
                return $this->_permissions;
            case Room::class:
                return $this->_rooms;
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
        throw new Exception('Group doesn\'t have any "many to one" relation!');
    }

}
