<?php

namespace SmartHome\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Selectable;
use JsonSerializable;
use SmartHome\Common\Abstracts\Entity;

/**
 * @Entity @Table(name="groups")
 * */
class Group extends Entity implements JsonSerializable {

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
     * @var ArrayCollection|User[]
     *
     * @ManyToMany(targetEntity="User", mappedBy="_groups")
     * @JoinTable(name="users_groups")
     */
    private $_users;

    /**
     * @var ArrayCollection|Permission[]
     *
     * @ManyToMany(targetEntity="Permission", inversedBy="_groups")
     * @JoinTable(name="groups_permissions")
     */
    private $_permissions;

    /**
     * @var ArrayCollection|Room[]
     *
     * @ManyToMany(targetEntity="Room", inversedBy="_groups")
     * @JoinTable(name="groups_rooms")
     */
    private $_rooms;

    public function __construct () {
        $this->_users = new ArrayCollection();
        $this->_permissions = new ArrayCollection();
        $this->_rooms = new ArrayCollection();
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

    public function getUsers () {
        return $this->_users;
    }

    public function getPermissions () {
        return $this->_permissions;
    }

    public function getRooms () {
        return $this->_rooms;
    }

    public function getCollection (string $entityName): Selectable {
        $this->checkEntityName($entityName);
        switch ($entityName) {
            case User::class:
                return $this->_users;
            case Permission::class:
                return $this->_permissions;
            case Room::class:
                return $this->_rooms;
            default :
                throw new Exception('Device doesn\'t have relation to '.$entityName);
        }
    }

    public function setRelation (string $entityName, ?Entity $value): self {
        throw new Exception('Group doesn\'t have any "many to one" relation!');
    }

}
