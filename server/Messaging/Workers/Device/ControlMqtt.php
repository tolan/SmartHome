<?php

namespace SmartHome\Messaging\Workers\Device;

use SmartHome\Messaging\Abstracts\AWorker;
use SmartHome\Common\{
    MQTT,
    Utils\JSON
};
use SmartHome\Enum\{
    Topic,
    ControlType
};
use SmartHome\Entity\{
    Device,
    Module,
    Control
};
use SmartHome\Rest\Device\Helper\Control as ControlHelper;
use SmartHome\Messaging\Exception;
use SmartHome\Database\EntityQuery;
use SmartHome\Common\Service;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class ControlMqtt extends AWorker {

    /**
     *
     * @var Service
     */
    private $_commonService;

    /**
     * @var MQTT
     */
    private $_mqtt;

    /**
     *
     * @var array
     */
    private $_registred = [];

    /**
     * @Inject({"container", "mqtt"})
     */
    public function __construct ($container, $mqtt, Service $service) {
        parent::__construct($container);
        $this->_mqtt = $mqtt;
        $this->_commonService = $service;

        $this->call();
    }

    public function prepare () {
        $topics = [
            Topic::DEVICE_CONTROL_MQTT => [
                'function' => function (string $topic, string $message) {
                    $this->call();
                },
            ],
        ];
        $this->subscribe($topics);
    }

    private function call () {
        $this->unsubscribe(array_column($this->_registred, 'topic'));
        $this->_registred = [];

        $query = EntityQuery::create(Device::class, [[Module::class, Control::class]]);
        $devices = $this->_commonService->find($query);
        foreach ($devices as $device) { /* @var $device Device */
            $this->_loadDevice($device);
        }

        $topics = [];
        $registred = $this->_registred;
        foreach ($registred as $subscriber) {
            $topics[$subscriber['topic']] = [
                'qos' => 0,
                'function' => function ($topic, $message) use ($registred) {
                    foreach ($registred as $subscriber) {
                        if ($subscriber['topic'] === $topic) {
                            $subscriber['function']($topic, $message);
                        }
                    }
                },
            ];
        }

        $this->subscribe($topics);
    }

    private function _loadDevice (Device $device) {
        foreach ($device->getModules()->toArray() as $module) { /* @var $module Module */
            $this->_loadModule($device, $module);
        }
    }

    private function _loadModule (Device $device, Module $module) {
        foreach ($module->getControls()->toArray() as $control) { /* @var $control Control */
            if ($control->getType() === ControlType::MQTT) {
                $this->_loadControl($device, $module, $control);
            }
        }
    }

    private function _loadControl (Device $device, Module $module, Control $control) {
        $controlData = $control->getControlData();
        $mqttData = $controlData['mqtt'];

        foreach ($mqttData as $mqtt) {
            ['topic' => $topic, 'type' => $type, 'value' => $value] = $mqtt;
            $this->_registred[] = [
                'topic' => $topic,
                'function' => function ($topic, $message) use ($type, $value, $device, $module) {
                    $data = (array)JSON::decode($message);
                    if (empty($data)) {
                        return;
                    }

                    $this->_commonService->clear();

                    $targetId = $module->getControls()->filter(function(Control $element) use ($type) {
                                        return $element->getType() === $type;
                                    })->first()->getId();

                    $query = EntityQuery::create(Control::class, [], ['id' => $targetId]);
                    $target = $this->_commonService->findOne($query); /* @var $target Control */

                    $controlData = $target->getControlData();

                    $value = array_key_exists($value, $data) ? $data[$value] : $value;
                    if ($type === ControlType::SWITCH) {
                        $controlData['value'] = (bool)$value;
                    } elseif ($type === ControlType::PWM) {
                        $controlData['value'] = (int)$value;
                    } elseif ($type === ControlType::FADE) {
                        $controlData['value'] = (int)$value;
                        $controlData['running'] = !$controlData['running'];
                    } else {
                        throw new Exception('Unsupported control type!');
                    }

                    if ($data['data']) {
                        $controlData = array_replace_recursive($controlData, $data['data']);
                    }

                    $target->setControlData($controlData);

                    $this->_commonService->persist($target, true);

                    ControlHelper::syncControls($this->_commonService, $device, $module, $target);
                    ControlHelper::sendControlUpdate($this->_mqtt, $device, $module, $target);
                },
            ];
        }
    }

}
