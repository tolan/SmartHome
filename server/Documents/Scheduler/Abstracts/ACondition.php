<?php

namespace SmartHome\Documents\Scheduler\Abstracts;

use SmartHome\Common\Abstracts\Document;
use SmartHome\Documents\Scheduler\Condition;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use SmartHome\Enum\Scheduler\Condition\Type;
use Exception;
use JsonSerializable;

/**
 * This file defines abstract class for condition of task document.
 *
 * @ODM\Document(collection="conditions")
 * @ODM\InheritanceType("SINGLE_COLLECTION")
 * @ODM\DiscriminatorField("type")
 * @ODM\DiscriminatorMap({
 *      Type::LAST_RUN=Condition\LastRun::class,
 *      Type::PING=Condition\Ping::class,
 *      Type::TIME=Condition\Time::class,
 *      Type::VALUE=Condition\Value::class,
 *      Type::OR=Condition\OrCondition::class
 * })
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
abstract class ACondition extends Document implements JsonSerializable {

    /**
     * ID
     *
     * @var string
     *
     * @ODM\Id
     */
    protected $id;

    /**
     * Value
     *
     * @var array
     *
     * @ODM\Field(type="hash")
     */
    protected $value;

    /**
     * Returns data for JSON serialize.
     *
     * @return array
     */
    public function jsonSerialize() {
        return [
            'id'    => $this->getId(),
            'type'  => $this->getType(),
            'value' => $this->getValue(),
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
     * Gets condition value
     *
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * Sets condition value
     *
     * @param mixed $value Condition value
     *
     * @return void
     */
    public function setValue($value): void {
        $this->value = $value;
    }

    /**
     * Gets condition type
     *
     * @return string|null
     */
    public function getType(): ?string {
        $type = null;
        switch (true) {
            case $this instanceof Condition\LastRun:
                $type = Type::LAST_RUN;
                break;
            case $this instanceof Condition\Ping:
                $type = Type::PING;
                break;
            case $this instanceof Condition\Time:
                $type = Type::TIME;
                break;
            case $this instanceof Condition\Value:
                $type = Type::VALUE;
                break;
            case $this instanceof Condition\OrCondition:
                $type = Type::OR;
                break;
        }

        return $type;
    }

    /**
     * Factory method for create condition by type
     *
     * @param string $type Condition type
     *
     * @return ACondition
     *
     * @throws Exception
     */
    public static function createCondition(string $type): ACondition {
        $condition = null;
        switch ($type) {
            case Type::LAST_RUN:
                $condition = new Condition\LastRun();
                break;
            case Type::PING:
                $condition = new Condition\Ping();
                break;
            case Type::TIME:
                $condition = new Condition\Time();
                break;
            case Type::VALUE:
                $condition = new Condition\Value();
                break;
            case Type::OR:
                $condition = new Condition\OrCondition();
                break;
            default:
                throw new Exception('Unsupported condition type.');
        }

        return $condition;
    }

}
