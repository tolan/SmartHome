<?php

namespace SmartHome\Rest\Device\Helper;

use SmartHome\Common\MQTT;
use SmartHome\Common\Utils\JSON;
use SmartHome\Enum\Topic;
use SmartHome\Entity\Device;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Firmware {

    public static function sentFirmwareUpdate (Device $device, MQTT $mqtt) {
        $data = [
            'device' => $device,
        ];

        $mqtt->publish(Topic::FIRMWARE_UPGRADE, JSON::encode($data));
    }

}
