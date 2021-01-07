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
 * This file defines class for firmware upgrade worker
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class FirmwareUpgrade extends AWorker {

    const ACTIVE_TIMEOUT = 60;

    /**
     * Common service
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
        $this->_service = $container->get(Service::class);
        $this->_logger  = $container->get('logger');
        $this->_mqtt    = $container->get('mqtt');
    }

    /**
     * Prepare worker
     *
     * @return void
     */
    public function prepare() {
        $topics = [
            Topic::FIRMWARE_UPGRADE => [
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
    public function receive(string $topic, string $message) {
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

    /**
     * Loads affected devices and firmware by given message contains related id
     *
     * @param array $message Array with firmware or device and their id
     *
     * @return array
     */
    private function _getDevicesAndFirmware($message) {
        $data   = JSON::decode($message);
        $result = [[], null];
        if (array_key_exists('firmware', $data)) {
            $query    = EntityQuery::create(Firmware::class, [[Device::class]], ['id' => $data['firmware']['id']]);
            $firmware = $this->_service->findOne($query); /* @var $firmware Firmware */
            $devices  = $firmware->getDevices()->toArray();
            $result   = [$devices, $firmware];
        } else if (array_key_exists('device', $data)) {
            $query    = EntityQuery::create(Device::class, [[Firmware::class]], ['id' => $data['device']['id']]);
            $device   = $this->_service->findOne($query); /* @var $device Device */
            $firmware = $device->getFirmware();
            $result   = [[$device], $firmware];
        }

        return $result;
    }

    /**
     * Sends firmware to device via http post message
     *
     * @param Device   $device  Device
     * @param Firmware $firmare Firmware
     *
     * @return void
     */
    private function _sendFirmware(Device $device, Firmware $firmare) {
        try {
            $address  = 'http://'.$device->getIpAddress().'/update';
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
