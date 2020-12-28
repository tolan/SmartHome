<?php

namespace SmartHome\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Selectable;
use JsonSerializable;
use SmartHome\Common\Abstracts\Entity;

/**
 * @Entity @Table(name="users")
 * */
class User extends Entity implements JsonSerializable {

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
    protected $username;

    /**
     * @Column(type="string")
     *
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $token;

    /**
     * @Column(type="string")
     *
     * @var string
     */
    protected $apiToken;

    /**
     * @var ArrayCollection|Group[]
     *
     * @ManyToMany(targetEntity="Group", inversedBy="_users")
     * @JoinTable(name="users_groups")
     */
    private $_groups;

    public function __construct () {
        $this->_groups = new ArrayCollection();
    }

    public function jsonSerialize () {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'token' => $this->token,
            'apiToken' => $this->apiToken,
        ];
    }

    public function getId () {
        return $this->id;
    }

    public function getUserName () {
        return $this->username;
    }

    public function setUsername ($username) {
        $this->username = $username;
    }

    public function getPassword () {
        return $this->password;
    }

    public function setPassword ($password) {
        $this->password = $password;
    }

    public function getToken () {
        return $this->token;
    }

    public function setToken ($token) {
        $this->token = $token;
    }

    public function getApiToken () {
        return $this->apiToken;
    }

    public function setApiToken ($token) {
        $this->apiToken = $token;
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
        throw new Exception('User doesn\'t have any "many to one" relation!');
    }

}
