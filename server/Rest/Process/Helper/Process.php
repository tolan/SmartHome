<?php

namespace SmartHome\Rest\Process\Helper;

use SmartHome\Common\MQTT;
use SmartHome\Enum\ProcessControlAction;
use SmartHome\Enum\Topic;
use SmartHome\Common\Utils\JSON;

/**
 * This file defines class for process helper.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Process {

    /**
     * Sends restart message
     *
     * @param MQTT   $mqtt MQTT client
     * @param string $id   Process id
     *
     * @return void
     */
    public static function sendRestart(MQTT $mqtt, string $id) {
        $content = [
            'action' => ProcessControlAction::RESTART,
        ];
        $mqtt->publish(Topic::PROCESS_CONTROL.'/'.$id, JSON::encode($content));
    }

}
