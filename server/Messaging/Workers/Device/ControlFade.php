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
use SmartHome\Scheduler\Trace;

/**
 * This file defines class for control fade worker
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class ControlFade extends AWorker {

    /**
     * Common servce
     *
     * @var Service
     */
    private $_service;

    /**
     * Logger
     *
     * @var Logger
     */
    private $_logger;

    /**
     * Transformation helper
     *
     * @var Fade\Transformation
     */
    private $_transformation;

    /**
     * Construct method for inject container
     *
     * @param Container $container Container
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->_service        = $container->get(Service::class);
        $this->_logger         = $container->get('logger');
        $this->_transformation = new Fade\Transformation(3);
    }

    /**
     * Prepare worker
     *
     * @return void
     */
    public function prepare() {
        $topics = [
            Topic::DEVICE_CONTROL_FADE => [
                'function' => function (string $topic, string $message) {
                    $this->receive($topic, $message);
                },
            ],
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
        $data     = JSON::decode($message);
        ['device' => $device, 'module' => $module, 'control' => $control, 'traceId' => $traceId] = $data;

        $query         = EntityQuery::create(Control::class, [], ['id' => $control['id']]);
        $controlEntity = $this->_service->findOne($query); /* @var $controlEntity ControlEntity */
        $controlData   = $controlEntity->getControlData();

        try {
            if ($controlData['running']) {
                $this->_syncControls($module, $control);
                $this->_sendUpdate($device, $module, $control, ($traceId ?? Trace::getNewId()));
            }

            $this->_establishTimer($module, $control);
        } catch (Exception $ex) {
            $this->_logger->error($ex->getMessage());
        }
    }

    /**
     * Makes synchronization between controls for module
     *
     * @param array $module  Array with id
     * @param array $control Array with controlData
     *
     * @throws Exception
     *
     * @return void
     */
    private function _syncControls($module, $control) {
        $query        = EntityQuery::create(Module::class, [[Control::class]], ['id' => $module['id']]);
        $moduleEntity = $this->_service->findOne($query); /* @var $moduleEntity Module */
        $controls     = $moduleEntity->getControls();
        $settinsData  = $moduleEntity->getSettingsData();
        $controlData  = $control['controlData'];
        $duration     = ($controlData['delay']['value'] * (($controlData['delay']['unit'] === 'min') ? 60 : 1));
        $passed       = (strtotime('now') - strtotime($controlData['created']['date']));

        $filterPwm = function(Control $element) {
            return $element->getType() === ControlType::PWM;
        };
        $pwm = $controls->filter($filterPwm)->first(); /* @var $pwm Control */

        if ($pwm && $passed <= $duration) {
            $pwmControlData = $pwm->getControlData();
            $initValue      = $controlData['initValue'];
            $targetValue    = $controlData['value'];
            $maxValue       = (pow(2, ($settinsData['resolution'] ?? 8)) - 1);
            $oldValue       = $pwmControlData['previous'];
            $newValue       = $this->_transformation->getValue($maxValue, $initValue, $targetValue, $passed, $duration);

            if ($oldValue !== $newValue) {
                $pwmControlData['previous'] = $pwmControlData['value'];
                $pwmControlData['value']    = $newValue;

                $pwm->setControlData($pwmControlData);
                $this->_logger->info(
                    'Control fade has chenged value from '.$oldValue.' to '.$newValue.'.',
                    [$maxValue, $initValue, $targetValue, $passed, $duration]
                );

                $this->_service->persist($pwm);

                $filterSw = function(Control $element) {
                    return $element->getType() === ControlType::SWITCH;
                };
                $switch = $controls->filter($filterSw)->first(); /* @var $switch Control */

                if ($switch) {
                    $switchControlData          = $switch->getControlData();
                    $switchControlData['value'] = (bool)$newValue;
                    $switch->setControlData($switchControlData);

                    $this->_service->persist($switch);
                }
            }

            $this->_service->flush();
        } else if (!$pwm) {
            throw new Exception('Module '.$moduleEntity->getId().' does not have PWM control!');
        }
    }

    /**
     * Sends control message with updated value
     *
     * @param array  $device  Device array data
     * @param array  $module  Module array data
     * @param array  $control Control array data
     * @param string $traceId Trace ID
     *
     * @return void
     */
    private function _sendUpdate($device, $module, $control, $traceId) {
        $query        = EntityQuery::create(Module::class, [[Control::class]], ['id' => $module['id']]);
        $moduleEntity = $this->_service->findOne($query); /* @var $moduleEntity Module */

        $controls = $moduleEntity->getControls();

        $filter = function(Control $element) {
            return $element->getType() === ControlType::PWM;
        };
        $pwm = $controls->filter($filter)->first(); /* @var $pwm Control */
        $pwmControlData = $pwm->getControlData();

        if (!array_key_exists('previous', $pwmControlData) || $pwmControlData['previous'] !== $pwmControlData['value']) {
            $control['type']                    = ControlType::PWM;
            $control['controlData']['value']    = $pwmControlData['value'];
            $control['controlData']['previous'] = ($pwmControlData['previous'] ?? $pwmControlData['value']);

            unset($control['controlData']['delay']);
            unset($control['controlData']['created']);

            $data = [
                'device'  => $device,
                'module'  => $module,
                'control' => $control,
                'traceId' => $traceId,
            ];

            $this->publish(Topic::DEVICE_CONTROL, JSON::encode($data), 0, 0, true);
        }
    }

    /**
     * Establishes timer
     *
     * @param array $module  array with id
     * @param array $control Control data array
     *
     * @return void
     */
    private function _establishTimer($module, $control) {
        $controlData  = $control['controlData'];
        $duration     = ($controlData['delay']['value'] * (($controlData['delay']['unit'] === 'min') ? 60 : 1));
        $passed       = (strtotime('now') - strtotime($controlData['created']['date']));
        $restDuration = ($duration - $passed);

        $query             = EntityQuery::create(Control::class, [], ['id' => $control['id']]);
        $controlEntity     = $this->_service->findOne($query); /* @var $controlEntity ControlEntity */
        $entityControlData = $controlEntity->getControlData();

        if ($restDuration < 0 || $entityControlData['running'] === false) {
            $entityControlData['running'] = false;

            $controlEntity->setControlData($entityControlData);
            $this->_service->persist($controlEntity, true);

            $timer = new Timer();
            $timer->setName('device_control_fade_'.$module['id']);
            $timer->setTimeout('0');

            $this->publish(Topic::TIMER_STOP, JSON::encode($timer), 0, 0, false);
        }
    }

}
