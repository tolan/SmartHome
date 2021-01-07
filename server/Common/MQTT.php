<?php

namespace SmartHome\Common;

use Bluerhinos\phpMQTT;

/**
 * This file defines class for MQTT client
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class MQTT {

    /**
     * Client of phpMQTT
     *
     * @var phpMQTT
     */
    private $_mqtt;

    /**
     * Construct method for inject dependecy
     *
     * @param phpMQTT $mqtt phpMQTT client
     */
    public function __construct(phpMQTT $mqtt) {
        $this->_mqtt = $mqtt;
    }

    /**
     * Descrtruct client
     *
     * @return void
     */
    public function __destruct() {
        $this->_mqtt->close();
    }

    /**
     * Subscribes topics
     *
     * @param array $topics Array in format [topic => callback]
     * @param int   $qos    QoS
     *
     * @return void
     */
    public function subscribe(array $topics, int $qos = 0) {
        return $this->_mqtt->subscribe($topics, $qos);
    }

    /**
     * Unsubscribes topics
     *
     * @param array $topics Array in format [topic => any]
     *
     * @return void
     */
    public function unsubscribe(array $topics) {
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

    /**
     * Publishes messace to topic
     *
     * @param string $topic   Topic
     * @param string $content Content
     * @param int    $qos     QoS
     * @param int    $retain  Retain
     *
     * @return void
     */
    public function publish(string $topic, string $content, int $qos = 0, int $retain = 0) {
        return $this->_mqtt->publish($topic, $content, $qos, $retain);
    }

    /**
     * Process mqtt messages (receive)
     *
     * @param bool $loop Make in loop
     *
     * @return boolean|string
     */
    public function proc(bool $loop = true) {
        return $this->_mqtt->proc($loop);
    }

    /**
     * Makes ping (keep alive) to mqtt broker
     *
     * @return void
     */
    public function ping() {
        return $this->_mqtt->ping();
    }

    /**
     * Makes reconnection to mqtt broker
     *
     * @return boolean
     */
    public function reconnect() {
        $this->_mqtt->close();
        return $this->_mqtt->connect();
    }

    /**
     * Close connection
     *
     * @return void
     */
    public function close() {
        return $this->_mqtt->close();
    }

}
