<?php

namespace SmartHome\Rest\Device\Helper;

use SmartHome\Common\MQTT;
use SmartHome\Common\Utils\JSON;
use SmartHome\Entity\Device;
use SmartHome\Enum\Topic;

/**
 * This file defines class for device restart helper.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Restart {

    /**
     * Sends device restart
     *
     * @param MQTT   $mqtt   MQTT client
     * @param Device $device Device
     *
     * @return void
     */
    public static function send(MQTT $mqtt, Device $device) {
        $mqtt->publish(Topic::DEVICE_RESTART, JSON::encode($device));
    }

}
