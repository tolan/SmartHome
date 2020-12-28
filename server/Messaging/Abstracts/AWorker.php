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
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
abstract class AWorker implements IWorker {

    use Traits\TProcessState;
    use Traits\TKeepAlive;

    const QOS_DEFAULT = 0;
    const ACTIVE_TIMEOUT = 30;

    /**
     *
     * @var MQTT
     */
    private $_mqtt;

    /**
     *
     * @var Service
     */
    private $_service;

    /**
     *
     * @var string
     */
    private $_id = null;

    public function __construct (Container $container) {
        $this->_mqtt = $container->get('mqtt');
        $this->_service = $container->get(Service::class);
        $this->_id = $container->get('taskId');
        $this->prepare();
        $this->sendProcessState($this->_mqtt, ProcessTaskState::START, $this->_id);
    }

    final public function setId (string $id): AWorker {
        $this->_id = $id;
    }

    final protected function publish (string $topic, string $content, int $qos = 0, int $retain = 0): AWorker {
        $this->_mqtt->publish($topic, $content, $qos, $retain);

        return $this;
    }

    final protected function subscribe (array $topics, int $qos = self::QOS_DEFAULT) {
        $wrappedTopics = [];
        $self = $this;
        foreach ($topics as $topic => ['qos' => $qos, 'function' => $function]) {
            $wrappedTopics[$topic] = [
                'qos' => is_null($qos) ? $qos : self::QOS_DEFAULT,
                'function' => function ($topic, $message) use ($function) {
                    $this->sendProcessState($this->_mqtt, ProcessTaskState::ACTIVE, $this->_id);
                    $function($topic, $message);
                    $this->sendProcessState($this->_mqtt, ProcessTaskState::INACTIVE, $this->_id);
                },
            ];
        }

        $qos = $qos ? $qos : self::QOS_DEFAULT;
        $this->_mqtt->subscribe($topics, $qos);
    }

    final protected function unsubscribe (array $topics) {
        $this->_mqtt->unsubscribe($topics);
    }

    public function proc (): AWorker {
        $this->_mqtt->proc();
        $this->process();
        $this->sendKeepAlive($this->_mqtt, $this->_id);
        return $this;
    }

    protected function process () {
        $this->_service->flush();
        $this->_service->clear();
    }

    abstract protected function prepare ();
}
