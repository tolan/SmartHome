<?php

namespace SmartHome\Event\Abstracts;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
abstract class AMessage {

    private $_data;

    public function __construct ($data) {
        $this->_data = $data;
    }

    public function getData () {
        return $this->_data;
    }

}
