<?php

namespace SmartHome\Messaging\Workers\Device;

use Laminas\Http\Client;
use SmartHome\Messaging\Abstracts\AWorker;
use SmartHome\Common\Utils\JSON;
use SmartHome\Common\MQTT;
use SmartHome\Enum\Topic;
use SmartHome\Entity\{
    Firmware,
    Device
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
class FirmwareUpgrade extends AWorker {

    const ACTIVE_TIMEOUT = 60;

    /**
     * @var Service
     */
    private $_service;

    /**
     * @var Logger
     */
    private $_logger;

    /**
     * @var MQTT
     */
    private $_mqtt;

    public function __construct (Container $container) {
        parent::__construct($container);
        $this->_service = $container->get(Service::class);
        $this->_logger = $container->get('logger');
        $this->_mqtt = $container->get('mqtt');
    }

    public function prepare () {
        $topics = [
            Topic::FIRMWARE_UPGRADE => [
                'function' => function (string $topic, string $message) {
                    $this->receive($topic, $message);
                },
            ],
        ];
        $this->subscribe($topics);
    }

    public function receive (string $topic, string $message) {
        [$devices, $firmware] = $this->_getDevicesAndFirmware($message);

        foreach ($devices as $device) { /* @var $device Device */
            if (!$firmware) {
                break;
            }

            $attempts = 3;
            while ($attempts) {
                try {
                    $this->_sendFirmware($device, $firmware);
                    $this->_mqtt->ping();
                    $attempts = 0;
                } catch (Exception $e) {
                    $this->_mqtt->ping();
                    sleep(10);
                    $attempts--;
                }
            }
        }
    }

    private function _getDevicesAndFirmware ($message) {
        $data = JSON::decode($message);
        $result = [[], null];
        if (array_key_exists('firmware', $data)) {
            $query = EntityQuery::create(Firmware::class, [[Device::class]], ['id' => $data['firmware']['id']]);
            $firmware = $this->_service->findOne($query); /* @var $firmware Firmware */
            $devices = $firmware->getDevices()->toArray();
            $result = [$devices, $firmware];
        } elseif (array_key_exists('device', $data)) {
            $query = EntityQuery::create(Device::class, [[Firmware::class]], ['id' => $data['device']['id']]);
            $device = $this->_service->findOne($query); /* @var $device Device */
            $firmware = $device->getFirmware();
            $result = [[$device], $firmware];
        }

        return $result;
    }

    private function _sendFirmware (Device $device, Firmware $firmare) {
        try {
            $address = 'http://'.$device->getIpAddress().'/update';
            $filename = $firmare->getDir().'/'.$firmare->getFilename();

            $client = new Client();
            $client->setUri($address);
            $client->setOptions([
                'timeout' => 30,
            ]);
            $client->setFileUpload($filename, 'update');
            $client->setMethod('POST');

            $response = $client->send();
            $this->_logger->info('Firmware upload to device '.$device->getId().' is completed: '.$response->getBody());
        } catch (Exception $e) {
            $this->_logger->error('Firmware upload failed: '.$e->getMessage());
        }
    }

}
