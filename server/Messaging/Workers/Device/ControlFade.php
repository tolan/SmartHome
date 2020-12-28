<?php

namespace SmartHome\Messaging\Workers\Device;

use SmartHome\Messaging\Abstracts\AWorker;
use SmartHome\Common\Utils\{
    JSON
};
use SmartHome\Entity\{
    Module,
    Control,
    Timer
};
use SmartHome\Enum\{
    Topic,
    ControlType
};
use Monolog\Logger;
use Exception;
use DI\Container;
use SmartHome\Common\Service;
use SmartHome\Database\EntityQuery;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class ControlFade extends AWorker {

    /**
     * @var Service
     */
    private $_service;

    /**
     * @var Logger
     */
    private $_logger;

    public function __construct (Container $container) {
        parent::__construct($container);
        $this->_service = $container->get(Service::class);
        $this->_logger = $container->get('logger');
    }

    public function prepare () {
        $topics = [
            Topic::DEVICE_CONTROL_FADE => [
                'function' => function (string $topic, string $message) {
                    $this->receive($topic, $message);
                },
            ],
        ];
        $this->subscribe($topics);
    }

    protected function receive (string $topic, string $message) {
        $data = JSON::decode($message);
        ['device' => $device, 'module' => $module, 'control' => $control] = $data;

        $query = EntityQuery::create(Control::class, [], ['id' => $control['id']]);
        $controlEntity = $this->_service->findOne($query); /* @var $controlEntity ControlEntity */
        $controlData = $controlEntity->getControlData();

        try {
            if ($controlData['running']) {
                $this->_syncControls($device, $module, $control);
                $this->_sendUpdate($device, $module, $control);
            }

            $this->_establishTimer($device, $module, $control);
        } catch (Exception $ex) {
            $this->_logger->error($ex->getMessage());
        }
    }

    private function _syncControls ($device, $module, $control) {
        $query = EntityQuery::create(Module::class, [[Control::class]], ['id' => $module['id']]);
        $moduleEntity = $this->_service->findOne($query); /* @var $moduleEntity Module */
        $controls = $moduleEntity->getControls();
        $controlData = $control['controlData'];
        $duration = $controlData['delay']['value'] * ($controlData['delay']['unit'] === 'min' ? 60 : 1);
        $passed = strtotime('now') - strtotime($controlData['created']['date']);

        $pwm = $controls->filter(function(Control $element) {
                    return $element->getType() === ControlType::PWM;
                })->first(); /* @var $pwm Control */

        if ($pwm && $passed <= $duration) {
            $pwmControlData = $pwm->getControlData();
            $initValue = $controlData['initValue'];
            $targetValue = $controlData['value'];
            $newValue = round($initValue + ($targetValue - $initValue) * ($passed / $duration));

            $pwmControlData['value'] = $newValue;
            $pwm->setControlData($pwmControlData);

            $this->_service->persist($pwm);

            $switch = $controls->filter(function(Control $element) {
                        return $element->getType() === ControlType::SWITCH;
                    })->first(); /* @var $switch Control */

            if ($switch) {
                $switchControlData = $switch->getControlData();
                $switchControlData['value'] = (bool)$newValue;
                $switch->setControlData($switchControlData);

                $this->_service->persist($switch);
            }

            $this->_service->flush();
        } elseif (!$pwm) {
            throw new Exception('Module '.$moduleEntity->getId().' does not have PWM control!');
        }
    }

    private function _sendUpdate ($device, $module, $control) {
        $query = EntityQuery::create(Module::class, [[Control::class]], ['id' => $module['id']]);
        $moduleEntity = $this->_service->findOne($query); /* @var $moduleEntity Module */

        $controls = $moduleEntity->getControls();

        $pwm = $controls->filter(function(Control $element) {
                    return $element->getType() === ControlType::PWM;
                })->first(); /* @var $pwm Control */

        $control['type'] = ControlType::PWM;
        $control['controlData']['value'] = $pwm->getControlData()['value'];
        unset($control['controlData']['delay']);
        unset($control['controlData']['created']);

        $data = [
            'device' => $device,
            'module' => $module,
            'control' => $control,
        ];

        $this->publish(Topic::DEVICE_CONTROL, JSON::encode($data));
    }

    private function _establishTimer ($device, $module, $control) {
        $controlData = $control['controlData'];
        $duration = $controlData['delay']['value'] * ($controlData['delay']['unit'] === 'min' ? 60 : 1);
        $passed = strtotime('now') - strtotime($controlData['created']['date']);
        $restDuration = $duration - $passed;

        $query = EntityQuery::create(Control::class, [], ['id' => $control['id']]);
        $controlEntity = $this->_service->findOne($query); /* @var $controlEntity ControlEntity */
        $entityControlData = $controlEntity->getControlData();

        if ($restDuration < 0 || $entityControlData['running'] === false) {
            $entityControlData['running'] = false;

            $controlEntity->setControlData($entityControlData);
            $this->_service->persist($controlEntity, true);

            $timer = new Timer();
            $timer->setName('device_control_fade_'.$module['id']);
            $timer->setTimeout('0');

            $this->publish(Topic::TIMER_STOP, JSON::encode($timer));
        }
    }

}
