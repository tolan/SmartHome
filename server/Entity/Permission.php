<?php

namespace SmartHome\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Selectable;
use JsonSerializable;
use SmartHome\Common\Abstracts\Entity;

/**
 * @Entity @Table(name="permissions")
 * */
class Permission extends Entity implements JsonSerializable {

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
     * @Column(type="string")
     *
     * @var string
     */
    protected $type;

    /**
     * @var ArrayCollection|Group[]
     *
     * @ManyToMany(targetEntity="Group", mappedBy="_permissions")
     * @JoinTable(name="groups_permissions")
     */
    private $_groups;

    public function __construct () {
        $this->_groups = new ArrayCollection();
    }

    public function jsonSerialize () {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
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

    public function getType () {
        return $this->type;
    }

    public function setType ($type) {
        return $this->type = $type;
    }

    public function getGroups () {
        return $this->_groups;
    }

    public function getCollection (string $entityName): Selectable {
        $this->checkEntityName($entityName);
        switch ($entityName) {
            case Group::class:
                return $this->_groups;
            default :
                throw new Exception('Device doesn\'t have relation to '.$entityName);
        }
    }

    public function setRelation (string $entityName, ?Entity $value): self {
        throw new Exception('Permission doesn\'t have any "many to one" relation!');
    }

}
