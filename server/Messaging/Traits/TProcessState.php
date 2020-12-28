<?php

namespace SmartHome\Messaging\Traits;

use SmartHome\Common\{
    MQTT,
    Utils\JSON
};
use SmartHome\Enum\Topic;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
trait TProcessState {

    protected function sendProcessState (MQTT $mqtt, string $state, string $id) {
        $mqtt->publish(Topic::PROCESS_STATE.'/'.$id, JSON::encode([
                    'state' => $state,
                    'timestamp' => microtime(true),
        ]));
    }

}
