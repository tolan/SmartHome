<?php

namespace SmartHome\Rest\Device\Helper;

use SmartHome\Common\MQTT;
use SmartHome\Common\Utils\JSON;
use SmartHome\Entity\Device;
use SmartHome\Enum\Topic;

/**
 * This file defines class for device registration helper.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Registration {

    /**
     * Sends registration
     *
     * @param Device $device Device
     * @param MQTT   $mqtt   MQTT client
     *
     * @return void
     */
    public static function sendRegistration(Device $device, MQTT $mqtt) {
        $mqtt->publish(Topic::DEVICE_REGISTRATION, JSON::encode($device));
    }

}
