<?php

namespace SmartHome\Messaging\Traits;

use SmartHome\Enum\ProcessTaskState;
use SmartHome\Common\MQTT;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
trait TKeepAlive {

    use TProcessState;

    private static $KEEP_ALIVE_TIMEOUT = 10;

    private $_lastTimestamp = null;

    protected function sendKeepAlive(MQTT $mqtt, string $id) {
        if (microtime(true) - self::$KEEP_ALIVE_TIMEOUT > $this->_lastTimestamp) {
            $this->_lastTimestamp = microtime(true);
            $this->sendProcessState($mqtt, ProcessTaskState::KEEP_ALIVE, $id);
        }
    }
}
