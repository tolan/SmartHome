<?php

namespace SmartHome\Messaging\Workers\Device;

use SmartHome\Messaging\Abstracts\AWorker;
use SmartHome\Common\Utils\JSON;
use SmartHome\Enum\Topic;
use GuzzleHttp\Client;
use Monolog\Logger;
use Exception;
use DI\Container;

/**
 * This file defines class for restart worker
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Restart extends AWorker {

    /**
     * Logger instance
     *
     * @var Logger
     */
    private $_logger;

    /**
     * Construct method for inject container
     *
     * @param Container $container Container
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->_logger = $container->get('logger');
    }

    /**
     * Prepare worker
     *
     * @return void
     */
    public function prepare() {
        $topics = [
            Topic::DEVICE_RESTART => [
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
        $device = JSON::decode($message);

        $client = new Client();

        try {
            $address  = 'http://'.$device['ipAddress'].'/restart';
            $response = $client->get($address);
            $this->_logger->info('Device restart success: '.$response->getBody());
        } catch (Exception $e) {
            $this->_logger->error('Device restart error: '.$e->getMessage());
        }
    }

}
