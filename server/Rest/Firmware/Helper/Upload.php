<?php

namespace SmartHome\Rest\Firmware\Helper;

use SmartHome\Common\MQTT;
use SmartHome\Common\Utils\JSON;
use SmartHome\Enum\Topic;
use SmartHome\Entity\Firmware;

/**
 * This file defines class for firmware upload helper
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Upload {

    /**
     * Sends firmware update message
     *
     * @param Firmware $firmware Firmware
     * @param MQTT     $mqtt     MQTT client
     *
     * @return void
     */
    public static function sentFirmwareUpdate(Firmware $firmware, MQTT $mqtt) {
        $data = [
            'firmware' => $firmware,
        ];

        $mqtt->publish(Topic::FIRMWARE_UPGRADE, JSON::encode($data), 0, 0, true);
    }

}
