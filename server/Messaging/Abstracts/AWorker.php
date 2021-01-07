<?php

namespace SmartHome\Messaging\Abstracts;

use SmartHome\Messaging\Interfaces\IWorker;
use SmartHome\Messaging\Traits;
use SmartHome\Common\{
    MQTT,
    Service
};
use SmartHome\Enum\ProcessTaskState;
use DI\Container;

/**
 * This file defines abstarct class for messaging worker
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
abstract class AWorker implements IWorker {

    use Traits\TProcessState;
    use Traits\TKeepAlive;

    const QOS_DEFAULT    = 0;
    const ACTIVE_TIMEOUT = 30;

    /**
     * MQTT client
     *
     * @var MQTT
     */
    private $_mqtt;

    /**
     * Common service
     *
     * @var Service
     */
    private $_service;

    /**
     * Worker ID
     *
     * @var string
     */
    private $_id = null;

    /**
     * Construct method for inject container
     *
     * @param Container $container Container
     */
    public function __construct(Container $container) {
        $this->_mqtt    = $container->get('mqtt');
        $this->_service = $container->get(Service::class);
        $this->_id      = $container->get('taskId');
        $this->prepare();
        $this->sendProcessState($this->_mqtt, ProcessTaskState::START, $this->_id);
    }

    /**
     * Sets Worker ID
     *
     * @param string $id Worker ID
     *
     * @return AWorker
     */
    final public function setId(string $id): AWorker {
        $this->_id = $id;
    }

    /**
     * Publishes message to mqtt.
     *
     * @param string $topic   Topic name
     * @param string $content Message content
     * @param int    $qos     QoS
     * @param int    $retain  Retain
     *
     * @return AWorker
     */
    final protected function publish(string $topic, string $content, int $qos = 0, int $retain = 0): AWorker {
        $this->_mqtt->publish($topic, $content, $qos, $retain);

        return $this;
    }

    /**
     * Subscribes topics
     *
     * @param array $topics Array of topics in format [[topic: string] => [function: closure(topic, message), qos: int]
     * @param int   $qos    QoS
     *
     * @return void
     */
    final protected function subscribe(array $topics, int $qos = self::QOS_DEFAULT) {
        $wrappedTopics = [];
        foreach ($topics as $topic => ['qos' => $topicQos, 'function' => $function]) {
            $wrappedTopics[$topic] = [
                'qos'      => !is_null($topicQos) ? $topicQos : $qos,
                'function' => function ($topic, $message) use ($function) {
                    $this->sendProcessState($this->_mqtt, ProcessTaskState::ACTIVE, $this->_id);
                    $function($topic, $message);
                    $this->sendProcessState($this->_mqtt, ProcessTaskState::INACTIVE, $this->_id);
                },
            ];
        }

        $this->_mqtt->subscribe($topics, $qos);
    }

    /**
     * Unsubscribes topics
     *
     * @param array $topics Array of topics in format [[topic: string] => mixed]
     *
     * @return void
     */
    final protected function unsubscribe(array $topics) {
        $this->_mqtt->unsubscribe($topics);
    }

    /**
     * Receives message
     *
     * @return AWorker
     */
    public function proc(): AWorker {
        $this->_mqtt->proc();
        $this->process();
        $this->sendKeepAlive($this->_mqtt, $this->_id);
        return $this;
    }

    /**
     * Process worker
     *
     * @return void
     */
    protected function process() {
        $this->_service->flush();
        $this->_service->clear();
    }

    /**
     * Abstract method for prepare worker
     *
     * @return void
     */
    abstract protected function prepare();

}
