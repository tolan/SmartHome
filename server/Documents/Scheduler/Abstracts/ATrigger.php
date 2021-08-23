<?php

namespace SmartHome\Documents\Scheduler\Abstracts;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Common\Collections\ArrayCollection;
use SmartHome\Common\Abstracts\Document;
use SmartHome\Documents\Scheduler\Trigger;
use SmartHome\Documents\Scheduler\Task;
use SmartHome\Enum\Scheduler\Trigger\Type;
use Exception;
use JsonSerializable;
use MongoDB\BSON\UTCDateTime;
use DateTimeZone;
use SmartHome\Common\Utils;

/**
 * This file defines abstract class for trigger of task document.
 *
 * @ODM\Document(collection="triggers")
 * @ODM\InheritanceType("SINGLE_COLLECTION")
 * @ODM\DiscriminatorField("type")
 * @ODM\DiscriminatorMap({
 *      Type::EVENT=Trigger\Event::class,
 *      Type::MQTT=Trigger\Mqtt::class,
 *      Type::TIME=Trigger\Time::class
 * })
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
abstract class ATrigger extends Document implements JsonSerializable {

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
     * DateTime of last run
     *
     * @var UTCDateTime|null
     *
     * @ODM\Field(type="date")
     */
    protected $lastRun;

    /**
     * Parent task
     *
     * @var Task
     *
     * @ODM\ReferenceOne(targetDocument=Task::class)
     */
    protected $task;

    /**
     * Assigned outputs
     *
     * @var ArrayCollection
     *
     * @ODM\ReferenceMany(targetDocument=AOutput::class)
     */
    protected $output;

    /**
     * Assigned conditions
     *
     * @var ArrayCollection
     *
     * @ODM\ReferenceMany(targetDocument=ACondition::class)
     */
    protected $conditions;

    /**
     * Contruct method
     */
    public function __construct() {
        $this->output     = new ArrayCollection();
        $this->conditions = new ArrayCollection();
    }

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
     * Gets trigger data
     *
     * @return mixed
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Sets trigger data
     *
     * @param mixed $data Trigger data
     *
     * @return void
     */
    public function setData($data): void {
        $this->data = $data;
    }

    /**
     * Gets last run
     *
     * @return Utils\DateTime
     */
    public function getLastRun(): Utils\DateTime {
        $timestamp = $this->lastRun;
        if ($timestamp) {
            $timestamp = ($this->lastRun instanceof UTCDateTime) ? $this->lastRun->toDateTime()->getTimestamp() : $this->lastRun->getTimestamp();
        }

        return ($timestamp) ? (new Utils\DateTime())->setTimestamp($timestamp)->setTimezone(new DateTimeZone('UTC')) : $timestamp;
    }

    /**
     * Sets datetime of last run
     *
     * @param UTCDateTime $date Datetime
     *
     * @return void
     */
    public function setLastRun(UTCDateTime $date): void {
        $this->lastRun = $date;
    }

    /**
     * Gets trigger type
     *
     * @return string|null
     */
    public function getType(): ?string {
        $type = null;
        switch (true) {
            case $this instanceof Trigger\Event:
                $type = Type::EVENT;
                break;
            case $this instanceof Trigger\Mqtt:
                $type = Type::MQTT;
                break;
            case $this instanceof Trigger\Time:
                $type = Type::TIME;
                break;
        }

        return $type;
    }

    /**
     * Gets associated outputs
     *
     * @return ArrayCollection
     */
    public function getOutput() {
        return $this->output;
    }

    /**
     * Gets associated conditions
     *
     * @return ArrayCollection
     */
    public function getConditions() {
        return $this->conditions;
    }

    /**
     * Gets parent task
     *
     * @return Task
     */
    public function getTask(): Task {
        return $this->task;
    }

    /**
     * Sets parent task
     *
     * @param Task $task Parent task
     *
     * @return void
     */
    public function setTask(Task $task): void {
        $this->task = $task;
    }

    /**
     * Returns identity message.
     *
     * @return string
     */
    abstract public function getMessage(): string;

    /**
     * Factory method for create trigger by type
     *
     * @param string $type Trigger type
     *
     * @return ATrigger
     *
     * @throws Exception
     */
    public static function createTrigger(string $type): ATrigger {
        $trigger = null;
        switch ($type) {
            case Type::EVENT:
                $trigger = new Trigger\Event();
                break;
            case Type::MQTT:
                $trigger = new Trigger\Mqtt();
                break;
            case Type::TIME:
                $trigger = new Trigger\Time();
                break;
            default:
                throw new Exception('Unsupported trigger type.');
        }

        return $trigger;
    }

}
