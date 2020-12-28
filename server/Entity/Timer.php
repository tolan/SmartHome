<?php

namespace SmartHome\Entity;

use JsonSerializable;
use SmartHome\Common\Utils\DateTime;
use Exception;
use SmartHome\Common\Abstracts\Entity;
use Doctrine\Common\Collections\Selectable;

/**
 * @Entity @Table(name="timers")
 */
class Timer extends Entity implements JsonSerializable {

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
    protected $topic;

    /**
     * @Column(type="json")
     *
     * @var string
     */
    protected $content;

    /**
     * @Column{type="datetime"}
     *
     * @var DateTime
     */
    protected $created;

    /**
     * @Column{type="string", nullable=true}
     *
     * @var string
     */
    protected $lastRun = '';

    /**
     * @Column(type="string")
     *
     * @var string
     */
    protected $timeout;

    /**
     * @Column(type="boolean")
     *
     * @var bool
     */
    protected $repated = false;

    public function __construct (array $data = null) {
        $this->created = new DateTime();
        if ($data) {
            $this->name = $data['name'];
            $this->topic = $data['topic'];
            $this->content = $data['content'];
            $this->created = new DateTime($data['created']);
            $this->timeout = $data['timeout'];
            $this->repated = $data['repeated'];
        }
    }

    public function jsonSerialize () {
        if (!$this->name) {
            throw new Exception('Name must be set!');
        }

        return [
            'name' => $this->name,
            'topic' => $this->topic,
            'content' => $this->content,
            'created' => strval($this->created),
            'timeout' => $this->timeout,
            'repeated' => $this->repated,
        ];
    }

    public function getId (): ?string {
        return $this->id;
    }

    public function setId (string $id) {
        $this->id = $id;
    }

    public function getName (): string {
        return $this->name;
    }

    public function setName (string $name) {
        $this->name = $name;
    }

    public function getTargetTopic (): string {
        return $this->topic;
    }

    public function setTargetTopic (string $topic) {
        $this->topic = $topic;
    }

    public function getContent () {
        return $this->content;
    }

    public function setContent ($data) {
        $this->content = $data;
    }

    public function getCreated () {
        return $this->created;
    }

    public function setCreated (DateTime $dateTime) {
        $this->created = $dateTime;
    }

    public function getLastRun () {
        return $this->lastRun !== '' ? new DateTime($this->lastRun) : null;
    }

    public function setLastRun (DateTime $dateTime = null) {
        $this->lastRun = $dateTime;
    }

    public function getTimeout () {
        return $this->timeout;
    }

    public function setTimeout (string $timeout) {
        $this->timeout = $timeout;
    }

    public function isRepeated (): bool {
        return $this->repated;
    }

    public function setRepeated (bool $repeated = true) {
        $this->repated = $repeated;
    }

    public function getCollection (string $entityName): Selectable {
        throw new Exception('Timer doesn\'t have any many to many relation!');
    }

    public function setRelation (string $entityName, ?Entity $value): self {
        throw new Exception('Timer doesn\'t have any "many to one" relation!');
    }

}
