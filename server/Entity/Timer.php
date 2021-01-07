<?php

namespace SmartHome\Entity;

use JsonSerializable;
use SmartHome\Common\Utils\DateTime;
use Exception;
use SmartHome\Common\Abstracts\Entity;
use Doctrine\Common\Collections\Selectable;

/**
 * This file defines class for timer entity.
 *
 * @Entity @Table(name="timers")
 */
class Timer extends Entity implements JsonSerializable {

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
     * Topic
     *
     * @var string
     *
     * @Column(type="string")
     */
    protected $topic;

    /**
     * Content
     *
     * @var string
     *
     * @Column(type="json")
     */
    protected $content;

    /**
     * Created
     *
     * @var DateTime
     *
     * @Column{type="datetime"}
     */
    protected $created;

    /**
     * Last run
     *
     * @var string
     *
     * @Column{type="string", nullable=true}
     */
    protected $lastRun = '';

    /**
     * Timeout
     *
     * @var string
     *
     * @Column(type="string")
     */
    protected $timeout;

    /**
     * Repeated
     *
     * @var boolean
     *
     * @Column(type="boolean")
     */
    protected $repated = false;

    /**
     * Contruct method
     *
     * @param array $data Init data
     */
    public function __construct(array $data = null) {
        $this->created = new DateTime();
        if ($data) {
            $this->name    = $data['name'];
            $this->topic   = $data['topic'];
            $this->content = $data['content'];
            $this->created = new DateTime($data['created']);
            $this->timeout = $data['timeout'];
            $this->repated = $data['repeated'];
        }
    }

    /**
     * Returns serialized data from JSON serialize.
     *
     * @return array
     *
     * @throws Exception Throws when name is not set
     */
    public function jsonSerialize() {
        if (!$this->name) {
            throw new Exception('Name must be set!');
        }

        return [
            'name'     => $this->name,
            'topic'    => $this->topic,
            'content'  => $this->content,
            'created'  => strval($this->created),
            'timeout'  => $this->timeout,
            'repeated' => $this->repated,
        ];
    }

    /**
     * Gets Id
     *
     * @return int
     */
    public function getId(): ?string {
        return $this->id;
    }

    /**
     * Sets Id
     *
     * @param string $id ID
     *
     * @return $this
     */
    public function setId(string $id) {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets name
     *
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * Sets name
     *
     * @param string $name Name
     *
     * @return $this
     */
    public function setName(string $name) {
        $this->name = $name;
        return $this;
    }

    /**
     * Gets target topic
     *
     * @return string
     */
    public function getTargetTopic(): string {
        return $this->topic;
    }

    /**
     * Sets target topic
     *
     * @param string $topic Topic
     *
     * @return $this
     */
    public function setTargetTopic(string $topic) {
        $this->topic = $topic;
        return $this;
    }

    /**
     * Gets content
     *
     * @return array
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * Sets content
     *
     * @param array $data Data
     *
     * @return $this
     */
    public function setContent($data) {
        $this->content = $data;

        return $this;
    }

    /**
     * Gets created
     *
     * @return DateTime
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * Sets created
     *
     * @param DateTime $dateTime Date time
     *
     * @return $this
     */
    public function setCreated(DateTime $dateTime) {
        $this->created = $dateTime;
        return $this;
    }

    /**
     * Gets last run
     *
     * @return DateTime|null
     */
    public function getLastRun() {
        return ($this->lastRun !== '') ? new DateTime($this->lastRun) : null;
    }

    /**
     * Sets last run
     *
     * @param DateTime $dateTime Date time
     *
     * @return $this
     */
    public function setLastRun(DateTime $dateTime = null) {
        $this->lastRun = $dateTime;
        return $this;
    }

    /**
     * Gets timeout
     *
     * @return string
     */
    public function getTimeout() {
        return $this->timeout;
    }

    /**
     * Sets timeout
     *
     * @param string $timeout Timeout
     *
     * @return $this
     */
    public function setTimeout(string $timeout) {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Gets is repeated
     *
     * @return bool
     */
    public function isRepeated(): bool {
        return $this->repated;
    }

    /**
     * Sets is repeated
     *
     * @param bool $repeated Repeated
     *
     * @return $this
     */
    public function setRepeated(bool $repeated = true) {
        $this->repated = $repeated;
        return $this;
    }

    /**
     * Gets collection by entity name
     *
     * @param string $entityName Entity name
     *
     * @return void
     *
     * @throws Exception
     */
    public function getCollection(string $entityName): Selectable {
        throw new Exception('Timer doesn\'t have any many to many relation!');
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
        throw new Exception('Timer doesn\'t have any "many to one" relation!');
    }

}
