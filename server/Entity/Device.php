<?php

namespace SmartHome\Entity;

use SmartHome\Common\Utils\DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Selectable;
use JsonSerializable;
use SmartHome\Common\Abstracts\Entity;
use SmartHome\Common\Exception;

/**
 * This file defines class for device entity.
 *
 * @Entity @Table(name="devices")
 */
class Device extends Entity implements JsonSerializable {

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
     * Mac address
     *
     * @var string
     *
     * @Column(type="string")
     */
    protected $mac;

    /**
     * IP Address
     *
     * @var string
     *
     * @Column(type="string")
     */
    protected $ipAddress;

    /**
     * Last registration
     *
     * @var DateTime
     *
     * @Column{type="datetime"}
     */
    protected $lastRegistration;

    /**
     * Is active flag
     *
     * @var boolean
     *
     * @Column(type="boolean")
     */
    protected $isActive;

    /**
     * Firmware
     *
     * @var Firmware
     *
     * @ManyToOne(targetEntity="Firmware", inversedBy="_devices")
     * @JoinColumn(name="firmware_id",     referencedColumnName="id")
     */
    private $_firmware;

    /**
     * Room
     *
     * @var Room
     *
     * @ManyToOne(targetEntity="Room", inversedBy="_devices")
     * @JoinColumn(name="room_id",     referencedColumnName="id")
     */
    private $_room;

    /**
     * Modules
     *
     * @var ArrayCollection|Module[]
     *
     * @OneToMany(targetEntity="Module", mappedBy="_device")
     */
    private $_modules;

    /**
     * Contruct method
     */
    public function __construct() {
        $this->_modules = new ArrayCollection();
    }

    /**
     * Returns data for JSON serialize.
     *
     * @return array
     */
    public function jsonSerialize() {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'mac'              => $this->mac,
            'ipAddress'        => $this->ipAddress,
            'lastRegistration' => $this->lastRegistration,
            'isActive'         => $this->isActive,
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
    }

    /**
     * Gets MAC address
     *
     * @return string
     */
    public function getMac() {
        return $this->mac;
    }

    /**
     * Sets MAC address
     *
     * @param string $mac MAC address
     *
     * @return $this
     */
    public function setMac($mac) {
        $this->mac = $mac;
        return $this;
    }

    /**
     * Gets IP address
     *
     * @return string
     */
    public function getIpAddress() {
        return $this->ipAddress;
    }

    /**
     * Sets IP address
     *
     * @param string $ipAddress IP address
     *
     * @return $this
     */
    public function setIpAddress($ipAddress) {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    /**
     * Gets last registration
     *
     * @return DateTime
     */
    public function getLastRegistration() {
        return $this->lastRegistration;
    }

    /**
     * Sets last registration
     *
     * @param DateTime $dateTime Date time
     *
     * @return $this
     */
    public function setLastRegistration(DateTime $dateTime) {
        $this->lastRegistration = $dateTime;

        return $this;
    }

    /**
     * Gets is active
     *
     * @return bool
     */
    public function isActive(): bool {
        return $this->isActive;
    }

    /**
     * Sets is active
     *
     * @param bool $isActive is active flag
     *
     * @return $this
     */
    public function setIsActive(bool $isActive = true) {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * Gets firmware
     *
     * @return Firmware
     */
    public function getFirmware() {
        return $this->_firmware;
    }

    /**
     * Sets firmware
     *
     * @param Firmware $firmware Firmware
     *
     * @return $this
     */
    public function setFirmware($firmware) {
        $this->_firmware = $firmware;

        return $this;
    }

    /**
     * Gets room
     *
     * @return Room
     */
    public function getRoom() {
        return $this->_room;
    }

    /**
     * Sets room
     *
     * @param Room $room Room
     *
     * @return $this
     */
    public function setRoom($room) {
        $this->_room = $room;
        return $this;
    }

    /**
     * Gets modules
     *
     * @return ArrayCollection
     */
    public function getModules() {
        return $this->_modules;
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
            case Module::class:
                return $this->_modules;
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
            case Room::class:
                $this->_room     = $value;
                break;
            case Firmware::class:
                $this->_firmware = $value;
                break;
            default:
                throw new Exception('Device doesn\'t have relation to '.$entityName);
        }

        return $this;
    }

}
