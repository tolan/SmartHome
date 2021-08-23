<?php

namespace SmartHome\Messaging\Workers\Device;

use SmartHome\Messaging\Abstracts\AWorker;
use SmartHome\Common\Utils\{
    JSON,
    DateTime
};
use SmartHome\Entity\{
    Device,
    Module,
    Control,
    Timer
};
use SmartHome\Enum\Topic;
use SmartHome\Rest\Device\Helper\Control as ControlHelper;
use SmartHome\Common\MQTT;
use DateTimeZone;
use DI\Container;
use SmartHome\Common\Service;
use SmartHome\Database\EntityQuery;
use SmartHome\Messaging\Workers\Device\Module\Abstracts\Builder;

/**
 * This file defines class for registration worker
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Registration extends AWorker {

    const INIT = 'init';

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
     * Construct method for inject container
     *
     * @param Container $container Container
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->_mqtt    = $container->get('mqtt');
        $this->_service = $container->get(Service::class);
    }

    /**
     * Prepare worker
     *
     * @return void
     */
    public function prepare() {
        $topics                       = [
            Topic::DEVICE_REGISTRATION => [
                'function' => function (string $topic, string $message) {
                    $this->receive($topic, $message);
                },
            ],
            Topic::DEVICE_KEEP_ALIVE_FAIL => [
                'function' => function (string $topic, string $message) {
                    $this->receive($topic, $message);
                },
            ]
        ];
        $this->subscribe($topics);
    }

    /**
     * Receives message
     *
     * @param string $topic   Topic
     * @param string $message Message
     *
     * @return void
     */
    protected function receive(string $topic, string $message) {
        $data = array_filter(JSON::decode($message));

        switch ($topic) {
            case Topic::DEVICE_REGISTRATION:
                $device = $this->_syncDb($data, true);
                $this->_establishTimer($device);
                $this->_initDevice($device);
                break;
            case Topic::DEVICE_KEEP_ALIVE_FAIL:
                $this->_syncDb($data, false);
                break;
        }
    }

    /**
     * Makes synchronization with database
     *
     * @param array   $data     Array with mac and ip address
     * @param boolean $isActive isActive flag
     *
     * @return Device
     */
    private function _syncDb($data, bool $isActive): Device {
        $query  = EntityQuery::create(Device::class, [], ['mac' => $data['mac']]);
        $device = $this->_service->findOne($query); /* @var $device Device */

        if (!$device) {
            $device = new Device();
            $device->setName('NoName');
            $device->setMac($data['mac']);
        }

        $device->setIpAddress($data['ipAddress']);
        $device->setLastRegistration((new DateTime())->setTimezone(new DateTimeZone('UTC')));
        $device->setIsActive($isActive);

        $this->_service->persist($device, true);

        return $device;
    }

    /**
     * Establishes keep alive timer
     *
     * @param Device $device Device
     *
     * @return void
     */
    private function _establishTimer(Device $device) {
        $timer = new Timer();
        $timer->setName('device_keep_alive_'.$device->getId());
        $timer->setTargetTopic(Topic::DEVICE_KEEP_ALIVE);
        $timer->setContent($device);
        $timer->setTimeout('10sec');
        $timer->setRepeated(true);

        $this->publish(Topic::TIMER_START, JSON::encode($timer));
    }

    /**
     * Sends initialization of device
     *
     * @param Device $device Device
     *
     * @return void
     */
    private function _initDevice(Device $device) {
        foreach ($device->getModules()->toArray() as $module) { /* @var $module Module */
            $builder = Builder::getBuilder($module); /* @var $builder Module\Abstracts\Builder */
            $control = $builder->prepareForInit(); /* @var $control Control */

            if ($control) {
                $firmvare = $device->getFirmware();
                if ($firmvare->getName() === 'v1.2.0') {
                    $control->setType($module->getType().'_'.self::INIT);
                } else {
                    $control->setType(self::INIT);
                }

                ControlHelper::sendControlUpdate($this->_mqtt, $device, $module, $control);
            }
        }
    }

}
