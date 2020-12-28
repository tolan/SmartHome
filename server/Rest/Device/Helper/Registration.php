<?php

namespace SmartHome\Rest\Device\Helper;

use SmartHome\Common\MQTT;
use SmartHome\Common\Utils\JSON;
use SmartHome\Entity\Device;
use SmartHome\Enum\Topic;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Registration {

    public static function sendRegistration (Device $device, MQTT $mqtt) {
        $mqtt->publish(Topic::DEVICE_REGISTRATION, JSON::encode($device));
    }

}
