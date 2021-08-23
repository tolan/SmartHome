<?php

namespace SmartHome\Documents\Scheduler;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Common\Collections\ArrayCollection;
use SmartHome\Common\Abstracts\Document;
use SmartHome\Documents\Scheduler\Abstracts\{
    ATrigger,
    ACondition,
    AAction
};
use JsonSerializable;
use DateTimeZone;
use MongoDB\BSON\UTCDateTime;
use SmartHome\Common\Utils;

/**
 * This file defines class for task document.
 *
 * @ODM\Document(collection="tasks")
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Task extends Document implements JsonSerializable {

    /**
     * ID
     *
     * @var string
     *
     * @ODM\Id
     */
    protected $id;

    /**
     * Name
     *
     * @var string
     *
     * @ODM\Field(type="string")
     */
    protected $name;

    /**
     * Enabled flag
     *
     * @var boolean
     *
     * @ODM\Field(type="boolean")
     */
    protected $enabled;

    /**
     * Share flag
     *
     * @var boolean
     *
     * @ODM\Field(type="boolean")
     */
    protected $share;

    /**
     * ID of the creator
     *
     * @var integer
     *
     * @ODM\Field(type="int")
     */
    protected $creatorId;

    /**
     * Datetime of last run
     *
     * @var UTCDateTime|null
     *
     * @ODM\Field(type="date")
     */
    protected $lastRun;

    /**
     * Assigned triggers
     *
     * @var ArrayCollection
     *
     * @ODM\ReferenceMany(targetDocument=ATrigger::class)
     */
    protected $triggers;

    /**
     * Assigned conditions
     *
     * @var ArrayCollection
     *
     * @ODM\ReferenceMany(targetDocument=ACondition::class)
     */
    protected $conditions;

    /**
     * Assigned actions
     *
     * @var ArrayCollection
     *
     * @ODM\ReferenceMany(targetDocument=AAction::class)
     */
    protected $actions;

    /**
     * Construct method
     */
    public function __construct() {
        $this->triggers   = new ArrayCollection();
        $this->conditions = new ArrayCollection();
        $this->actions    = new ArrayCollection();
    }

    /**
     * Returns data for JSON serialize.
     *
     * @return array
     */
    public function jsonSerialize() {
        return [
            'id'      => $this->getId(),
            'name'    => $this->getName(),
            'enabled' => $this->getEnabled(),
            'share'   => $this->getShare(),
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
     * Gets name
     *
     * @return string|null
     */
    public function getName(): ?string {
        return $this->name;
    }

    /**
     * Sets name
     *
     * @param string $name Name
     *
     * @return void
     */
    public function setName(string $name): void {
        $this->name = $name;
    }

    /**
     * Gets enabled
     *
     * @return bool
     */
    public function getEnabled(): bool {
        return !!$this->enabled;
    }

    /**
     * Set enabled
     *
     * @param bool $enabled Enabled
     *
     * @return void
     */
    public function setEnabled(bool $enabled): void {
        $this->enabled = $enabled;
    }

    /**
     * Gets share
     *
     * @return bool
     */
    public function getShare(): bool {
        return !!$this->share;
    }

    /**
     * Sets share
     *
     * @param bool $share Share
     *
     * @return void
     */
    public function setShare(bool $share): void {
        $this->share = $share;
    }

    /**
     * Gets creator id
     *
     * @return string|null
     */
    public function getCreatorId(): ?string {
        return $this->creatorId;
    }

    /**
     * Sets creator id
     *
     * @param int $creatorId Creator id
     *
     * @return void
     */
    public function setCreatorId(int $creatorId): void {
        $this->creatorId = $creatorId;
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
     * Sets last run
     *
     * @param UTCDateTime $date Datetime
     *
     * @return void
     */
    public function setLastRun(UTCDateTime $date): void {
        $this->lastRun = $date;
    }

    /**
     * Gets associated triggers.
     *
     * @return ArrayCollection
     */
    public function getTriggers() {
        return $this->triggers;
    }

    /**
     * Gets associated conditions.
     *
     * @return ArrayCollection
     */
    public function getConditions() {
        return $this->conditions;
    }

    /**
     * Gets associated actions.
     *
     * @return ArrayCollection
     */
    public function getActions() {
        return $this->actions;
    }

}
