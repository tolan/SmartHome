<?php

namespace SmartHome\Documents\Scheduler\Abstracts;

use SmartHome\Common\Abstracts\Document;
use SmartHome\Documents\Scheduler\Action;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use SmartHome\Enum\Scheduler\Action\Type;
use JsonSerializable;

/**
 * This file defines abstract class for action of task document.
 *
 * @ODM\Document(collection="actions")
 * @ODM\InheritanceType("SINGLE_COLLECTION")
 * @ODM\DiscriminatorField("type")
 * @ODM\DiscriminatorMap({
 *      Type::DEVICE=Action\Device::class,
 *      Type::HTTP=Action\Http::class,
 *      Type::MQTT=Action\Mqtt::class
 * })
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
abstract class AAction extends Document implements JsonSerializable {

    /**
     * ID
     *
     * @var string
     *
     * @ODM\Id
     */
    protected $id;

    /**
     * Data
     *
     * @var array
     *
     * @ODM\Field(type="hash")
     */
    protected $data;

    /**
     * Returns data for JSON serialize.
     *
     * @return array
     */
    public function jsonSerialize() {
        return [
            'id'   => $this->getId(),
            'type' => $this->getType(),
            'data' => $this->getData(),
        ];
    }

    /**
     * Gets Id
     *
     * @return string|null
     */
    public function getId(): ?string {
        return $this->id;
    }

    /**
     * Gets data
     *
     * @return mixed
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Sets data
     *
     * @param mixed $data Action data
     *
     * @return void
     */
    public function setData($data): void {
        $this->data = $data;
    }

    /**
     * Gets action type
     *
     * @return string|null
     */
    public function getType(): ?string {
        $type = null;
        switch (true) {
            case $this instanceof Action\Device:
                $type = Type::DEVICE;
                break;
            case $this instanceof Action\Mqtt:
                $type = Type::MQTT;
                break;
            case $this instanceof Action\Http:
                $type = Type::HTTP;
                break;
        }

        return $type;
    }

    /**
     * Factory method for create action by type
     *
     * @param string $type Action type
     *
     * @return AAction
     *
     * @throws Exception
     */
    public static function createAction(string $type): AAction {
        $condition = null;
        switch ($type) {
            case Type::DEVICE:
                $condition = new Action\Device();
                break;
            case Type::MQTT:
                $condition = new Action\Mqtt();
                break;
            case Type::HTTP:
                $condition = new Action\Http();
                break;
            default:
                throw new Exception('Unsupported action type.');
        }

        return $condition;
    }

}
