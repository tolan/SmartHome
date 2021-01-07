<?php

namespace SmartHome\Event\Abstracts;

/**
 * This file defines abstarct class for event messages.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
abstract class AMessage {

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

}
