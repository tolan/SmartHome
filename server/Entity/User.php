<?php

namespace SmartHome\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Selectable;
use JsonSerializable;
use SmartHome\Common\Abstracts\Entity;

/**
 * This file defines class for user entity.
 *
 * @Entity @Table(name="users")
 */
class User extends Entity implements JsonSerializable {

    /**
     * Id
     *
     * @var integer
     *
     * @Id @Column(type="integer") @GeneratedValue
     */
    protected $id;

    /**
     * Username
     *
     * @var string
     *
     * @Column(type="string")
     */
    protected $username;

    /**
     * Password
     *
     * @var string
     *
     * @Column(type="string")
     */
    protected $password;

    /**
     * Token
     *
     * @var string
     */
    protected $token;

    /**
     * API token
     *
     * @var string
     *
     * @Column(type="string")
     */
    protected $apiToken;

    /**
     * Groups
     *
     * @var ArrayCollection|Group[]
     *
     * @ManyToMany(targetEntity="Group", inversedBy="_users")
     * @JoinTable(name="users_groups")
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
            'id'       => $this->id,
            'username' => $this->username,
            'token'    => $this->token,
            'apiToken' => $this->apiToken,
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
     * Gets username
     *
     * @return string
     */
    public function getUserName() {
        return $this->username;
    }

    /**
     * Sets username
     *
     * @param string $username Username
     *
     * @return $this
     */
    public function setUsername($username) {
        $this->username = $username;
        return $this;
    }

    /**
     * Gets password
     *
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Sets password
     *
     * @param string $password Password
     *
     * @return $this
     */
    public function setPassword($password) {
        $this->password = $password;
        return $this;
    }

    /**
     * Gets token
     *
     * @return string
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * Sets token
     *
     * @param string $token token
     *
     * @return $this
     */
    public function setToken($token) {
        $this->token = $token;
        return $this;
    }

    /**
     * Gets API token
     *
     * @return string
     */
    public function getApiToken() {
        return $this->apiToken;
    }

    /**
     * Sets API token
     *
     * @param string $token API token
     *
     * @return $this
     */
    public function setApiToken($token) {
        $this->apiToken = $token;
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
        throw new Exception('User doesn\'t have any "many to one" relation!');
    }

}
