<?php

namespace SmartHome\Messaging\Workers\Device;

use SmartHome\Messaging\Abstracts\AWorker;
use SmartHome\Common\Utils\JSON;
use SmartHome\Entity\{
    Timer,
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

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Control extends AWorker {

    /**
     * @var Logger
     */
    private $_logger;

    /**
     * @var Service
     */
    private $_service;

    public function __construct (Container $container) {
        parent::__construct($container);
        $this->_logger = $container->get('logger');
        $this->_service = $container->get(Service::class);
    }

    public function prepare () {
        $topics = [
            Topic::DEVICE_CONTROL => [
                'function' => function (string $topic, string $message) {
                    $this->receive($topic, $message);
                },
            ]
        ];
        $this->subscribe($topics);
    }

    protected function receive (string $topic, string $message) {
        $data = JSON::decode($message);
        ['device' => $device, 'module' => $module, 'control' => $control] = $data;

        $delay = $control['controlData'] ? $control['controlData']['delay'] : null;

        if ($delay && $delay['value'] > 0) {
            $this->_establishTimer($device, $module, $control);
        } else {
            $this->_sendImediately($device, $module, $control);
        }
    }

    private function _sendImediately ($device, $module, $control) {
        $client = new Client();
        $attempts = 3;

        while ($attempts > 0) {
            try {
                $query = EntityQuery::create(ControlEntity::class, [], ['id' => $control['id']]);
                $controlEntity = $this->_service->findOne($query); /* @var $controlEntity ControlEntity */
                $controlData = $controlEntity->getControlData();
                if ($controlEntity->getType() !== ControlType::FADE && $controlData['delay'] && $controlData['delay']['value'] > 0) {
                    $controlData['delay']['value'] = 0;
                    $controlEntity->setControlData($controlData);

                    $this->_service->persist($controlEntity, true);
                }

                $address = 'http://'.$device['ipAddress'].'/api';
                $data = [
                    'action' => $control['type'],
                    'data' => [
                        'pin' => $module['settingsData']['pin'],
                        'resolution' => $module['settingsData']['resolution'] ?? 8,
                        'channel' => $module['settingsData']['channel'],
                        'value' => $control['controlData']['value'],
                    ],
                ];
                $options = [
                    RequestOptions::FORM_PARAMS => [
                        'data' => JSON::encode([
                            $data,
                        ]),
                    ],
                ];

                $response = $client->post($address, $options);
                $this->_logger->info('Control success: '.$response->getBody());
                break;
            } catch (Exception $e) {
                $this->_logger->error('Control error: '.$e->getMessage());
                $attempts--;
            }
        }

        if ($attempts === 0) {
            $this->_logger->error('Control device error. All attempts for device '.$device['id'].' are consumed.', [$device]);
        }
    }

    private function _establishTimer ($device, $module, $control) {
        $delay = $control['controlData']['delay'];
        unset($control['controlData']['delay']);
        $timer = new Timer();
        $timer->setName('device_control_delay_'.$device['id']);
        $timer->setTargetTopic(Topic::DEVICE_CONTROL);
        $timer->setContent([
            'device' => $device,
            'module' => $module,
            'control' => $control,
        ]);
        $timer->setTimeout($delay['value'].$delay['unit']);

        $this->publish(Topic::TIMER_START, JSON::encode($timer));
    }

}
