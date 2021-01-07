<?php

namespace SmartHome\Messaging\Traits;

use SmartHome\Enum\ProcessTaskState;
use SmartHome\Common\MQTT;

/**
 * This file defines trait for keep alive function
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
trait TKeepAlive {

    use TProcessState;

    /**
     * Keep alive timeout
     *
     * @var integer
     */
    private static $_KEEP_ALIVE_TIMEOUT = 10;

    /**
     * Last timestamp of sent process state
     *
     * @var float
     */
    private $_lastTimestamp = null;

    /**
     * Sends keep alive process state
     *
     * @param MQTT   $mqtt MQTT
     * @param string $id   Worker ID
     *
     * @return void
     */
    protected function sendKeepAlive(MQTT $mqtt, string $id) {
        $time = microtime(true);
        if (($time - self::$_KEEP_ALIVE_TIMEOUT) > $this->_lastTimestamp) {
            $this->_lastTimestamp = $time;
            $this->sendProcessState($mqtt, ProcessTaskState::KEEP_ALIVE, $id);
        }
    }

}
