<?php

namespace SmartHome\Common;

use Bluerhinos\phpMQTT;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class MQTT {

    /**
     * @var phpMQTT
     */
    private $_mqtt;

    public function __construct (phpMQTT $mqtt) {
        $this->_mqtt = $mqtt;
    }

    public function __destruct () {
        $this->_mqtt->close();
    }

    public function subscribe (array $topics, int $qos = 0) {
        return $this->_mqtt->subscribe($topics, $qos);
    }

    public function unsubscribe (array $topics) {
        if (count($topics)) {
            $restTopics = array_diff_key($this->_mqtt->topics, array_flip($topics));

            $this->_mqtt->close();
            $this->_mqtt->topics = [];
            $this->_mqtt->connect();
            if (count($restTopics)) {
                $this->_mqtt->subscribe($restTopics, 0);
            }
        }

        return;
    }

    public function publish (string $topic, string $content, int $qos = 0, int $retain = 0) {
        return $this->_mqtt->publish($topic, $content, $qos, $retain);
    }

    public function proc (bool $loop = true) {
        return $this->_mqtt->proc($loop);
    }

    public function ping () {
        return $this->_mqtt->ping();
    }

    public function reconnect () {
        $this->_mqtt->close();
        return $this->_mqtt->connect();
    }

    public function close () {
        return $this->_mqtt->close();
    }

}
