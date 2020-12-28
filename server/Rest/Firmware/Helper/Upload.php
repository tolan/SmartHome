<?php

namespace SmartHome\Rest\Firmware\Helper;

use SmartHome\Common\MQTT;
use SmartHome\Common\Utils\JSON;
use SmartHome\Enum\Topic;
use SmartHome\Entity\Firmware;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Upload {

    public static function sentFirmwareUpdate (Firmware $firmware, MQTT $mqtt) {
        $data = [
            'firmware' => $firmware,
        ];

        $mqtt->publish(Topic::FIRMWARE_UPGRADE, JSON::encode($data));
    }

}
