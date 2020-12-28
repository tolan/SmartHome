<?php

namespace SmartHome\Messaging\Workers\Device;

use SmartHome\Messaging\Abstracts\AWorker;
use SmartHome\Common\Utils\JSON;
use SmartHome\Enum\Topic;
use GuzzleHttp\Client;
use Monolog\Logger;
use Exception;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Restart extends AWorker {

    /**
     * @var Logger
     */
    private $_logger;

    /**
     * @Inject({"container", "logger"})
     */
    public function __construct ($container, $logger) {
        parent::__construct($container);
        $this->_logger = $logger;
    }

    public function prepare () {
        $topics = [
            Topic::DEVICE_RESTART => [
                'function' => function (string $topic, string $message) {
                    $this->receive($topic, $message);
                },
            ],
        ];
        $this->subscribe($topics);
    }

    public function receive (string $topic, string $message) {
        $device = JSON::decode($message);

        $client = new Client();

        try {
            $address = 'http://'.$device['ipAddress'].'/restart';
            $response = $client->get($address);
            $this->_logger->info('Device restart success: '.$response->getBody());
        } catch (Exception $e) {
            $this->_logger->error('Device restart error: '.$e->getMessage());
        }
    }

}
