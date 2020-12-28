<?php

namespace SmartHome\Entity;

use SmartHome\Common\Utils\DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Selectable;
use JsonSerializable;
use SmartHome\Common\Abstracts\Entity;
use SmartHome\Common\Exception;

/**
 * @Entity @Table(name="devices")
 * */
class Device extends Entity implements JsonSerializable {

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
    protected $mac;

    /**
     * @Column(type="string")
     *
     * @var string
     */
    protected $ipAddress;

    /**
     * @Column{type="datetime"}
     *
     * @var DateTime
     */
    protected $lastRegistration;

    /**
     *
     * @var Firmware
     *
     * @ManyToOne(targetEntity="Firmware", inversedBy="_devices")
     * @JoinColumn(name="firmware_id", referencedColumnName="id")
     */
    private $_firmware;

    /**
     *
     * @var Room
     *
     * @ManyToOne(targetEntity="Room", inversedBy="_devices")
     * @JoinColumn(name="room_id", referencedColumnName="id")
     */
    private $_room;

    /**
     * @var ArrayCollection|Module[]
     *
     * @OneToMany(targetEntity="Module", mappedBy="_device")
     */
    private $_modules;

    public function __construct () {
        $this->_modules = new ArrayCollection();
    }

    public function jsonSerialize () {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'mac' => $this->mac,
            'ipAddress' => $this->ipAddress,
            'lastRegistration' => $this->lastRegistration,
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

    public function getMac () {
        return $this->mac;
    }

    public function setMac ($mac) {
        $this->mac = $mac;
    }

    public function getIpAddress () {
        return $this->ipAddress;
    }

    public function setIpAddress ($ipAddress) {
        $this->ipAddress = $ipAddress;
    }

    public function getLastRegistration () {
        return $this->lastRegistration;
    }

    public function setLastRegistration (DateTime $dateTime) {
        $this->lastRegistration = $dateTime;
    }

    public function getFirmware () {
        return $this->_firmware;
    }

    public function setFirmware ($firmware) {
        $this->_firmware = $firmware;
    }

    public function getRoom () {
        return $this->_room;
    }

    public function setRoom ($room) {
        $this->_room = $room;
    }

    public function getModules () {
        return $this->_modules;
    }

    public function getCollection (string $entityName): Selectable {
        $this->checkEntityName($entityName);
        switch ($entityName) {
            case Module::class:
                return $this->_modules;
            default :
                throw new Exception('Device doesn\'t have relation to '.$entityName);
        }
    }

    public function setRelation (string $entityName, ?Entity $value): self {
        $this->checkEntityName($entityName);
        switch ($entityName) {
            case Room::class:
                $this->_room = $value;
                break;
            case Firmware::class:
                $this->_firmware = $value;
                break;
            default :
                throw new Exception('Device doesn\'t have relation to '.$entityName);
        }

        return $this;
    }

}
