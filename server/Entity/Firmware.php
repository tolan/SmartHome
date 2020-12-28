<?php

namespace SmartHome\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Selectable;
use JsonSerializable;
use SmartHome\Common\Abstracts\Entity;

/**
 * @Entity @Table(name="firmwares")
 * */
class Firmware extends Entity implements JsonSerializable {

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
    protected $filename;

    /**
     * @var ArrayCollection|Device[]
     *
     * @OneToMany(targetEntity="Device", mappedBy="_firmware")
     */
    private $_devices;

    public function __construct () {
        $this->_devices = new ArrayCollection();
    }

    public function jsonSerialize () {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'filename' => $this->filename,
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

    public function getFilename () {
        return $this->filename;
    }

    public function setFilename ($filename) {
        $this->filename = $filename;
    }

    public function getDevices () {
        return $this->_devices;
    }

    public function getDir (): string {
        $dir = __DIR__.'/../../firmwares';
        return $dir.'/'.$this->getId();
    }

    public function getCollection (string $entityName): Selectable {
        $this->checkEntityName($entityName);
        switch ($entityName) {
            case Device::class:
                return $this->_devices;
            default :
                throw new Exception('Device doesn\'t have relation to '.$entityName);
        }
    }

    public function setRelation (string $entityName, ?Entity $value): self {
        throw new Exception('Firmware doesn\'t have any "many to one" relation!');
    }

}
