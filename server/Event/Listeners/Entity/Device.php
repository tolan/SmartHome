<?php

namespace SmartHome\Event\Listeners\Entity;

use SmartHome\Event\Abstracts\AMessage;
use SmartHome\Service;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Device extends AListener {

    /**
     *
     * @var Service\Device
     */
    private $_service;

    public function __construct (Service\Device $service) {
        $this->_service = $service;
    }

    public function receive (AMessage $message) {
        $this->_service->receive($message);
    }

}
