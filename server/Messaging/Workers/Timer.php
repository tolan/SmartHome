<?php

namespace SmartHome\Messaging\Workers;

use SmartHome\Messaging\Abstracts\AWorker;
use SmartHome\Timer\Container as TimerContainer;
use SmartHome\Enum\Topic;
use SmartHome\Entity\Timer as TimerEntity;
use SmartHome\Common\Utils\JSON;
use DI\Container;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Timer extends AWorker {

    const TIMER_INTERVAL = 1;

    /**
     *
     * @var TimerContainer
     */
    private $_container;

    /**
     *
     * @var float
     */
    private $_lastTimestamp;

    public function __construct (Container $container) {
        parent::__construct($container);
        $this->_container = $container->get(TimerContainer::class);
    }

    public function prepare () {
        $topics = [
            Topic::TIMER_START => [
                'function' => function (string $topic, string $message) {
                    $this->start($topic, $message);
                },
            ],
            Topic::TIMER_STOP => [
                'function' => function (string $topic, string $message) {
                    $this->stop($topic, $message);
                },
            ],
        ];
        $this->subscribe($topics);
    }

    protected function process () {
        if (microtime(true) - 1 >= $this->_lastTimestamp) {
            $this->_lastTimestamp = floor(microtime(true));
            $this->_container->call($this->_lastTimestamp);
        }
    }

    protected function start (string $topic, string $message) {
        $timer = new TimerEntity(JSON::decode($message));
        $this->_container->add($timer);
    }

    protected function stop (string $topic, string $message) {
        $timer = new TimerEntity(JSON::decode($message));
        $this->_container->remove($timer);
    }

}
