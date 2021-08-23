<?php

namespace SmartHome\Rest\Device\Helper;

use SmartHome\Common\MQTT;
use SmartHome\Common\Utils\JSON;
use SmartHome\Enum\Topic;
use SmartHome\Entity\Device;

/**
 * This file defines class for firmware device helper
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Firmware {

    /**
     * Sends firmware update
     *
     * @param Device $device Device
     * @param MQTT   $mqtt   MQTT client
     *
     * @return void
     */
    public static function sendFirmwareUpdate(Device $device, MQTT $mqtt) {
        $data = [
            'device' => $device,
        ];

        $mqtt->publish(Topic::FIRMWARE_UPGRADE, JSON::encode($data), 0, 0, true);
    }

}
