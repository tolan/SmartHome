<?php

namespace SmartHome\Timer;

use DateTimeZone;
use SmartHome\Entity\Timer;
use SmartHome\Common\MQTT;
use SmartHome\Common\Utils\{
    DateTime,
    JSON
};
use DI\Container as DIContainer;
use SmartHome\Common\Service;
use SmartHome\Database\EntityQuery;

/**
 * This file defines class for collect Timers.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Container {

    /**
     * Common service
     *
     * @var Service
     */
    private $_service;

    /**
     * MQTT client
     *
     * @var MQTT
     */
    private $_mqtt;

    /**
     * List of timers
     *
     * @var Timer[]
     */
    private $_timers = [];

    /**
     * Construct method for inject dependencies
     *
     * @param Container $container Container
     */
    public function __construct(DIContainer $container) {
        $this->_service = $container->get(Service::class);
        $this->_mqtt    = $container->get('mqtt');

        $query  = EntityQuery::create(Timer::class);
        $timers = $this->_service->find($query);

        foreach ($timers as $timer) { /* @var $timer Timer */
            $this->_timers[$timer->getName()] = $timer;
        }
    }

    /**
     * Calls timers
     *
     * @param float $microtime Microtime
     *
     * @return void
     */
    public function call($microtime) {
        foreach ($this->_timers as $key => $timer) {
            $lastRun = (($timer->getLastRun()) ?? $timer->getCreated()); /* @var $lastRun \SmartHome\Common\Utils\DateTime */
            $offset  = (new DateTimeZone(date_default_timezone_get()))->getOffset($lastRun);

            if ((strtotime($lastRun.' +'.$timer->getTimeout()) - $offset) <= $microtime) {
                $this->_mqtt->publish($timer->getTargetTopic(), JSON::encode($timer->getContent()));

                if (!$timer->isRepeated()) {
                    $this->remove($timer);
                    unset($this->_timers[$key]);
                } else {
                    $timer->setLastRun(new DateTime(date(DateTime::FORMAT, $microtime)));
                    $this->_service->persist($timer, true);
                }
            }
        }
    }

    /**
     * Adds timer
     *
     * @param Timer $timer Timer
     *
     * @return Container
     */
    public function add(Timer $timer): Container {
        $timer                            = $this->_getAndMergeTimer($timer);
        $this->_timers[$timer->getName()] = $timer;

        $this->_service->persist($timer, true);

        return $this;
    }

    /**
     * Removes timer
     *
     * @param Timer $timer Timer
     *
     * @return Container
     */
    public function remove(Timer $timer): Container {
        unset($this->_timers[$timer->getName()]);
        $timer = $this->_getAndMergeTimer($timer);

        if ($timer->getId()) {
            $this->_service->remove($timer, true);
        }

        return $this;
    }

    /**
     * Gets existed timer and merge with given timer
     *
     * @param Timer $timer Timer
     *
     * @return Timer
     */
    private function _getAndMergeTimer(Timer $timer) {
        $query   = EntityQuery::create(Timer::class, [], ['name' => $timer->getName()]);
        $existed = $this->_service->findOne($query); /* @var $existed Timer */

        if ($existed) {
            $existed->setContent($timer->getContent());
            $existed->setCreated($timer->getCreated());
            $existed->setRepeated($timer->isRepeated());
            $existed->setTimeout($timer->getTimeout());
        }

        return ($existed) ? $existed : $timer;
    }

}
