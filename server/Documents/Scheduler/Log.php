<?php

namespace SmartHome\Documents\Scheduler;

use JsonSerializable;
use DateTimeZone;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MongoDB\BSON\UTCDateTime;
use SmartHome\Common\Abstracts\Document;
use SmartHome\Common\Utils;

/**
 * This file defines class for log document.
 *
 * @ODM\Document(collection="tasks_logs")
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Log extends Document implements JsonSerializable {

    /**
     * ID
     *
     * @var string
     *
     * @ODM\Id
     */
    protected $id;

    /**
     * Datetime of creation
     *
     * @var UTCDateTime
     *
     * @ODM\Field(type="date")
     */
    protected $created;

    /**
     * Parent task
     *
     * @var Task
     *
     * @ODM\ReferenceOne(targetDocument=Task::class)
     */
    protected $task;

    /**
     * Message
     *
     * @var string
     *
     * @ODM\Field(type="string")
     */
    protected $message;

    /**
     * Returns data for JSON serialize.
     *
     * @return array
     */
    public function jsonSerialize() {
        return [
            'id'      => $this->getId(),
            'created' => (string)$this->getCreated(),
            'message' => $this->getMessage(),
        ];
    }

    /**
     * Gets id
     *
     * @return string|null
     */
    public function getId(): ?string {
        return $this->id;
    }

    /**
     * Gets datetime of creation
     *
     * @return Utils\DateTime
     */
    public function getCreated(): Utils\DateTime {
        $timestamp = ($this->created instanceof UTCDateTime) ? $this->created->toDateTime()->getTimestamp() : $this->created->getTimestamp();

        return (new Utils\DateTime())->setTimestamp($timestamp)->setTimezone(new DateTimeZone('UTC'));
    }

    /**
     * Sets datetime of creation
     *
     * @param UTCDateTime $date Datetime
     *
     * @return void
     */
    public function setCreated(UTCDateTime $date): void {
        $this->created = $date;
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
     * Gets message
     *
     * @return string
     */
    public function getMessage(): string {
        return $this->message;
    }

    /**
     * Sets message
     *
     * @param string $message Message
     *
     * @return void
     */
    public function setMessage(string $message): void {
        $this->message = $message;
    }

}
