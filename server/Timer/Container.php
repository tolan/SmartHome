<?php

namespace SmartHome\Timer;

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
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Container {

    /**
     * @var Service
     */
    private $_service;

    /**
     * @var MQTT
     */
    private $_mqtt;

    /**
     * @var Timer[]
     */
    private $_timers = [];

    public function __construct (DIContainer $container) {
        $this->_service = $container->get(Service::class);
        $this->_mqtt = $container->get('mqtt');

        $query = EntityQuery::create(Timer::class);
        $timers = $this->_service->find($query);

        foreach ($timers as $timer) { /* @var $timer Timer */
            $this->_timers[$timer->getName()] = $timer;
        }
    }

    public function call ($microtime) {
        foreach ($this->_timers as $key => $timer) {
            $lastRun = $timer->getLastRun() ?? $timer->getCreated();

            if (strtotime($lastRun.' +'.$timer->getTimeout()) < $microtime) {
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

    public function add (Timer $timer): Container {
        $timer = $this->_getAndMergeTimer($timer);
        $this->_timers[$timer->getName()] = $timer;

        $this->_service->persist($timer, true);

        return $this;
    }

    public function remove (Timer $timer): Container {
        unset($this->_timers[$timer->getName()]);
        $timer = $this->_getAndMergeTimer($timer);

        if ($timer->getId()) {
            $this->_service->remove($timer, true);
        }

        return $this;
    }

    private function _getAndMergeTimer (Timer $timer) {
        $query = EntityQuery::create(Timer::class, [], ['name' => $timer->getName()]);
        $existed = $this->_service->findOne($query); /* @var $existed Timer */

        if ($existed) {
            $existed->setContent($timer->getContent());
            $existed->setCreated(new DateTime($timer->getCreated()));
            $existed->setRepeated($timer->isRepeated());
            $existed->setTimeout($timer->getTimeout());
        }

        return $existed ? $existed : $timer;
    }

}
