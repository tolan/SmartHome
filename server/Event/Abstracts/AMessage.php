<?php

namespace SmartHome\Event\Abstracts;

use JsonSerializable;

/**
 * This file defines abstarct class for event messages.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
abstract class AMessage implements JsonSerializable {

    /**
     * Message data
     *
     * @var mixed
     */
    private $_data;

    /**
     * Construct method for one time set message data.
     *
     * @param mixed $data Message data
     */
    public function __construct($data) {
        $this->_data = $data;
    }

    /**
     * Returns data of message
     *
     * @return mixed
     */
    public function getData() {
        return $this->_data;
    }

    /**
     * Returns data for JSON serialize.
     *
     * @return array
     */
    public function jsonSerialize() {
        return [
            'class' => get_class($this),
            'data'  => $this->_data,
        ];
    }

}
