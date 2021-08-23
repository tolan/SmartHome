<?php

namespace SmartHome\Documents\Scheduler\Abstracts;

use SmartHome\Common\Abstracts\Document;
use SmartHome\Documents\Scheduler\Output;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use SmartHome\Enum\Scheduler\Output\Type;
use JsonSerializable;

/**
 * This file defines abstract class for output of task document.
 *
 * @ODM\Document(collection="outputs")
 * @ODM\InheritanceType("SINGLE_COLLECTION")
 * @ODM\DiscriminatorField("type")
 * @ODM\DiscriminatorMap({
 *      Type::DEFAULTS=Output\Defaults::class,
 *      Type::CUSTOM=Output\Custom::class
 * })
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
abstract class AOutput extends Document implements JsonSerializable {

    /**
     * ID
     *
     * @var string
     *
     * @ODM\Id
     */
    protected $id;

    /**
     * Key
     *
     * @var string
     *
     * @ODM\Field(type="string")
     */
    protected $key;

    /**
     * Value
     *
     * @var string
     *
     * @ODM\Field(type="string")
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
            'key'   => $this->getKey(),
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
     * Gets key
     *
     * @return string
     */
    public function getKey(): string {
        return $this->key;
    }

    /**
     * Sets key
     *
     * @param string $key Key
     *
     * @return void
     */
    public function setKey(string $key): void {
        $this->key = $key;
    }

    /**
     * Gets value
     *
     * @return string|null
     */
    public function getValue(): ?string {
        return $this->value;
    }

    /**
     * Sets value
     *
     * @param string $value Value
     *
     * @return void
     */
    public function setValue(string $value): void {
        $this->value = $value;
    }

    /**
     * Gets output type
     *
     * @return string|null
     */
    public function getType(): ?string {
        $type = null;
        switch (true) {
            case $this instanceof Output\Defaults:
                $type = Type::DEFAULTS;
                break;
            case $this instanceof Output\Custom:
                $type = Type::CUSTOM;
                break;
        }

        return $type;
    }

    /**
     * Factory method for create output by type
     *
     * @param string $type Output type
     *
     * @return AOutput
     *
     * @throws Exception
     */
    public static function createOutput(string $type): AOutput {
        $output = null;
        switch ($type) {
            case Type::DEFAULTS:
                $output = new Output\Defaults();
                break;
            case Type::CUSTOM:
                $output = new Output\Custom();
                break;
            default:
                throw new Exception('Unsupported output type.');
        }

        return $output;
    }

}
