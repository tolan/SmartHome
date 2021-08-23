<?php

namespace SmartHome\Messaging\Workers\Device;

use SmartHome\Messaging\Abstracts\AWorker;
use SmartHome\Common\Utils\JSON;
use SmartHome\Entity\{
    Timer,
    Module,
    Control as ControlEntity
};
use SmartHome\Enum\{
    Topic,
    ControlType
};
use GuzzleHttp\{
    Client,
    RequestOptions
};
use Monolog\Logger;
use Exception;
use DI\Container;
use SmartHome\Common\Service;
use SmartHome\Database\EntityQuery;
use SmartHome\Scheduler\Trace;
use SmartHome\Messaging\Workers\Device\Module\Abstracts\Builder;

/**
 * This file defines class for control worker
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Control extends AWorker {

    /**
     * Logger
     *
     * @var Logger
     */
    private $_logger;

    /**
     * Common service
     *
     * @var Service
     */
    private $_service;

    /**
     * Construct method for inject container
     *
     * @param Container $container Container
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->_logger  = $container->get('logger');
        $this->_service = $container->get(Service::class);
    }

    /**
     * Prepare worker
     *
     * @return void
     */
    public function prepare() {
        $topics = [
            Topic::DEVICE_CONTROL => [
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
        $data     = JSON::decode($message);
        ['device' => $device, 'module' => $module, 'control' => $control, 'traceId' => $traceId] = $data;

        $delay = ($control['controlData']) ? $control['controlData']['delay'] : null;

        if ($delay && $delay['value'] > 0) {
            $this->_establishTimer($device, $module, $control, ($traceId ?? Trace::getNewId()));
        } else {
            $this->_sendImediately($device, $module, $control);
        }
    }

    /**
     * Sends http request imediately
     *
     * @param array $device  array with ip address
     * @param array $module  array with settings data (pin, resolution, channel)
     * @param array $control array with id, type controlData (value, ?previous)
     *
     * @return void
     */
    private function _sendImediately($device, $module, $control) {
        $client             = new Client();
        $attempts           = 5;
        $loggerInfoMessages = [];

        while ($attempts > 0) {
            try {
                $loggerInfoMessages[] = [
                    'msg'     => 'Control init for device '.$device['id'].'.',
                    'context' => [$device, $module, $control],
                ];
                $controlQuery         = EntityQuery::create(ControlEntity::class, [[Module::class]], ['id' => $control['id']]);
                $controlEntity        = $this->_service->findOne($controlQuery); /* @var $controlEntity ControlEntity */
                $moduleEntity         = $controlEntity->getModule(); /* @var $moduleEntity Module */

                $builder = Builder::getBuilder($moduleEntity);

                $controlData = $controlEntity->getControlData();
                if ($controlEntity->getType() !== ControlType::FADE && $controlData['delay'] && $controlData['delay']['value'] > 0) {
                    $controlData['delay']['value'] = 0;
                    $controlEntity->setControlData($controlData);

                    $this->_service->persist($controlEntity, true);
                }

                $address = 'http://'.$device['ipAddress'].'/api';
                $data    = $builder->build($control);

                $options = [
                    RequestOptions::FORM_PARAMS => [
                        'data' => JSON::encode([$data]),
                    ],
                ];

                $loggerInfoMessages[] = [
                    'msg'     => 'Control send to device '.$device['id'].'.',
                    'context' => [],
                ];
                $response             = $client->post($address, $options);
                $loggerInfoMessages[] = [
                    'msg'     => 'Control success: '.$response->getBody(),
                    'context' => $data,
                ];
                break;
            } catch (Exception $e) {
                $this->_logger->warning('Control warning: '.$e->getMessage(), [$control]);
                $attempts--;
                // wait for 100ms
                usleep(100 * 1000);
            }
        }

        foreach ($loggerInfoMessages as $message) {
            $this->_logger->info($message['msg'], $message['context']);
        }

        if ($attempts === 0) {
            $this->_logger->error('Control device error. All attempts for device '.$device['id'].' are consumed.', [$device]);
        }
    }

    /**
     * Establishes timer
     *
     * @param array  $device  Device data array
     * @param array  $module  Module data array
     * @param array  $control Control data array
     * @param string $traceId Trace Id
     *
     * @return void
     */
    private function _establishTimer($device, $module, $control, $traceId) {
        $delay = $control['controlData']['delay'];
        unset($control['controlData']['delay']);
        $timer = new Timer();
        $timer->setName('device_control_delay_'.$device['id']);
        $timer->setTargetTopic(Topic::DEVICE_CONTROL);
        $timer->setContent([
            'device'  => $device,
            'module'  => $module,
            'control' => $control,
            'traceId' => $traceId,
        ]);
        $timer->setTimeout($delay['value'].$delay['unit']);

        $this->publish(Topic::TIMER_START, JSON::encode($timer));
    }

}
