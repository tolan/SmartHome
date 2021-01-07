<?php

namespace SmartHome\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Selectable;
use JsonSerializable;
use SmartHome\Common\Abstracts\Entity;

/**
 * This file defines class for permission entity.
 *
 * @Entity @Table(name="permissions")
 */
class Permission extends Entity implements JsonSerializable {

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
     * Type
     *
     * @var string
     *
     * @Column(type="string")
     */
    protected $type;

    /**
     * Groups
     *
     * @var ArrayCollection|Group[]
     *
     * @ManyToMany(targetEntity="Group",     mappedBy="_permissions")
     * @JoinTable(name="groups_permissions")
     */
    private $_groups;

    /**
     * Contruct method
     */
    public function __construct() {
        $this->_groups = new ArrayCollection();
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
            'type' => $this->type,
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
        throw new Exception('Permission doesn\'t have any "many to one" relation!');
    }

}
