<?php

namespace SmartHome\Messaging\Workers;

use SmartHome\Messaging\Abstracts\AWorker;
use SmartHome\Timer\Container as TimerContainer;
use SmartHome\Enum\Topic;
use SmartHome\Entity\Timer as TimerEntity;
use SmartHome\Common\Utils\JSON;
use DI\Container;

/**
 * This file defines class for timer worker
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Timer extends AWorker {

    const TIMER_INTERVAL = 1;

    /**
     * Timer container
     *
     * @var TimerContainer
     */
    private $_container;

    /**
     * Last activation timestamp
     *
     * @var float
     */
    private $_lastTimestamp;

    /**
     * Construct method for inject container
     *
     * @param Container $container Container
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->_container = $container->get(TimerContainer::class);
    }

    /**
     * Prepares worker
     *
     * @return void
     */
    public function prepare() {
        $topics           = [
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

    /**
     * Process worker
     *
     * @return void
     */
    protected function process() {
        $time = microtime(true);
        if (($time - 1) >= $this->_lastTimestamp) {
            $this->_lastTimestamp = floor($time);
            $this->_container->call($this->_lastTimestamp);
        }
    }

    /**
     * Starts timer
     *
     * @param string $topic   Topic
     * @param string $message Message
     *
     * @return void
     */
    protected function start(string $topic, string $message) {
        $timer = new TimerEntity(JSON::decode($message));
        $this->_container->add($timer);
    }

    /**
     * Stops timer
     *
     * @param string $topic   Topic
     * @param string $message Message
     *
     * @return void
     */
    protected function stop(string $topic, string $message) {
        $timer = new TimerEntity(JSON::decode($message));
        $this->_container->remove($timer);
    }

}
