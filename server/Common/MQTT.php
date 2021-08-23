<?php

namespace SmartHome\Common;

use Bluerhinos\phpMQTT;
use SmartHome\Common\Utils\{
    JSON,
    Timer
};
use DI\Container;

/**
 * This file defines class for MQTT client
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class MQTT {

    /**
     * Container
     *
     * @var Container
     */
    private $_container;

    /**
     * Client of phpMQTT
     *
     * @var phpMQTT
     */
    private $_mqtt;

    /**
     * Construct method for inject dependecy
     *
     * @param phpMQTT   $mqtt      phpMQTT client
     * @param Container $container Container instance
     */
    public function __construct(phpMQTT $mqtt, Container $container) {
        $this->_mqtt      = $mqtt;
        $this->_container = $container;
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
        $wrappedTopics = [];
        foreach ($topics as $topic => ['qos' => $topicQos, 'function' => $function]) {
            $wrappedTopics[$topic] = [
                'qos'      => !is_null($topicQos) ? $topicQos : $qos,
                'function' => function ($topic, $message) use ($function) {
                    $meta = null;
                    if (JSON::isEncoded($message)) {
                        $decoded = JSON::decode($message);
                        if (is_array($decoded) && count(array_keys($decoded)) === 2
                            && array_key_exists('meta', $decoded) && array_key_exists('content', $decoded)
                        ) {
                            ['meta' => $meta, 'content' => $content] = $decoded;
                            $this->_preProcessMeta($topic, $meta, $content);
                            $message  = JSON::encode($content);
                        }
                    }

                    $function($topic, $message);
                    if ($meta) {
                        $this->_postProcessMeta($topic, $meta, $content);
                    }
                },
            ];
        }

        return $this->_mqtt->subscribe($wrappedTopics, $qos);
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
     * @param string $topic         Topic
     * @param mixed  $content       Content
     * @param int    $qos           QoS
     * @param int    $retain        Retain
     * @param bool   $isAllowedMeta is allowed meta
     *
     * @return void
     */
    public function publish(string $topic, $content, int $qos = 0, int $retain = 0, $isAllowedMeta = false) {
        if (JSON::isEncoded($content) && $isAllowedMeta) {
            $data = [
                'content' => JSON::decode($content),
                'meta'    => [
                    'timer' => Timer::toJson($this->_container->get('timer')),
                ],
            ];

            $content = JSON::encode($data);
        }

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

    /**
     * Prepare metadata before processing.
     *
     * @param string $topic   Topic
     * @param array  $meta    Metadata
     * @param mixed  $content Content data
     *
     * @return void
     */
    private function _preProcessMeta($topic, $meta, $content) {
        ['timer' => $timerData] = $meta;
        $timer  = $this->_container->get('timer'); /* @var $timer Timer */
        Timer::fromJson($timerData, $timer);
        $timer->mark('worker.start.'.$topic)->tick();
    }

    /**
     * Process metadata after processing.
     *
     * @param string $topic   Topic
     * @param array  $meta    Metadata
     * @param mixed  $content Content data
     *
     * @return void
     */
    private function _postProcessMeta($topic, $meta, $content) {
        $timer = $this->_container->get('timer'); /* @var $timer Timer */
        $timer->mark('worker.end.'.$topic)->tick();
        $timer->flush()->clear();
    }

}
