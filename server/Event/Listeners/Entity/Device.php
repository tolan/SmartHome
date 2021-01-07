<?php

namespace SmartHome\Event\Listeners\Entity;

use SmartHome\Event\Abstracts\AMessage;
use SmartHome\Service;

/**
 * This file defines listener class for devices.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Device extends AListener {

    /**
     * Device service
     *
     * @var Service\Device
     */
    private $_service;

    /**
     * Construct method for inject service.
     *
     * @param Service\Device $service Device service
     */
    public function __construct(Service\Device $service) {
        $this->_service = $service;
    }

    /**
     * Receives message
     *
     * @param AMessage $message Message
     *
     * @return void
     */
    public function receive(AMessage $message) {
        $this->_service->receive($message);
    }

}
