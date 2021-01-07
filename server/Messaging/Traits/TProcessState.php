<?php

namespace SmartHome\Messaging\Traits;

use SmartHome\Common\{
    MQTT,
    Utils\JSON
};
use SmartHome\Enum\Topic;

/**
 * This file defines trait for sending process state
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
trait TProcessState {

    /**
     * Sends process state
     *
     * @param MQTT   $mqtt  MQTT
     * @param string $state Process state
     * @param string $id    Worker ID
     *
     * @return void
     */
    protected function sendProcessState(MQTT $mqtt, string $state, string $id) {
        $data = [
            'state'     => $state,
            'timestamp' => microtime(true),
        ];
        $mqtt->publish(Topic::PROCESS_STATE.'/'.$id, JSON::encode($data));
    }

}
