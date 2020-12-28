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
use SmartHome\Enum\{
    Topic,
    ControlType
};
use SmartHome\Rest\Device\Helper\Control as ControlHelper;
use SmartHome\Common\MQTT;
use DateTimeZone;
use DI\Container;
use SmartHome\Common\Service;
use SmartHome\Database\EntityQuery;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Registration extends AWorker {

    const INIT = 'init';

    /**
     * @var Service
     */
    private $_service;

    /**
     * @var MQTT
     */
    private $_mqtt;

    public function __construct (Container $container) {
        parent::__construct($container);
        $this->_mqtt = $container->get('mqtt');
        $this->_service = $container->get(Service::class);
    }

    public function prepare () {
        $topics = [
            Topic::DEVICE_REGISTRATION => [
                'function' => function (string $topic, string $message) {
                    $this->receive($topic, $message);
                },
            ],
        ];
        $this->subscribe($topics);
    }

    public function receive (string $topic, string $message) {
        $data = array_filter(JSON::decode($message));

        $device = $this->_syncDB($data);
        $this->_establishTimer($device);
        $this->_initDevice($device);
    }

    private function _syncDB ($data): Device {
        $query = EntityQuery::create(Device::class, [], ['mac' => $data['mac']]);
        $device = $this->_service->findOne($query); /* @var $device Device */

        if (!$device) {
            $device = new Device();
            $device->setName('NoName');
            $device->setMac($data['mac']);
        }

        $device->setIpAddress($data['ipAddress']);
        $device->setLastRegistration((new DateTime())->setTimezone(new DateTimeZone('UTC')));

        $this->_service->persist($device, true);

        return $device;
    }

    private function _establishTimer (Device $device) {
        $timer = new Timer();
        $timer->setName('device_keep_alive_'.$device->getId());
        $timer->setTargetTopic(Topic::DEVICE_KEEP_ALIVE);
        $timer->setContent($device);
        $timer->setTimeout('10sec');
        $timer->setRepeated(true);

        $this->publish(Topic::TIMER_START, JSON::encode($timer));
    }

    private function _initDevice (Device $device) {
        foreach ($device->getModules()->toArray() as $module) { /* @var $module Module */
            $controls = $module->getControls();
            $switch = $controls->filter(function(Control $element) {
                        return $element->getType() === ControlType::SWITCH;
                    })->first(); /* @var $switch Control */

            if ($switch) {
                $control = clone $switch;

                $resolution = $module->getSettingsData()['resolution'] ?? 8;
                $controlData = $control->getControlData();
                $controlData['value'] = $controlData['value'] ? (2 ^ $resolution - 1) : 0;
                unset($controlData['delay']);
                $control->setControlData($controlData);

                $pwm = $controls->filter(function(Control $element) {
                            return $element->getType() === ControlType::PWM;
                        })->first(); /* @var $pwm Control */

                if ($pwm && $controlData['value']) {
                    $control->setControlData($pwm->getControlData());
                }

                $control->setType(self::INIT);
                ControlHelper::sendControlUpdate($this->_mqtt, $device, $module, $control);
            }
        }
    }

}
