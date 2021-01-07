<?php

namespace SmartHome\Messaging\Workers\Device;

use SmartHome\Messaging\Abstracts\AWorker;
use SmartHome\Enum\Topic;
use GuzzleHttp\Client;
use SmartHome\Common\Utils\JSON;
use SmartHome\Entity\Timer;
use Exception;
use Monolog\Logger;
use DI\Container;

/**
 * This file defines class for kepp alive worker
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class KeepAlive extends AWorker {

    const MAX_ATTEMPTS = 3;

    /**
     * Logger
     *
     * @var Logger
     */
    private $_logger;

    /**
     * List of address and attempts
     *
     * @var array
     */
    private $_attempts = [];

    /**
     * Contstruct method for inject dependecies.
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
            Topic::DEVICE_KEEP_ALIVE => [
                'function' => function (string $topic, string $message) {
                    $this->receive($topic, $message);
                },
            ],
        ];
        $this->subscribe($topics);
    }

    /**
     * Receive message
     *
     * @param string $topic   Topic
     * @param string $message Message
     *
     * @return void
     */
    protected function receive(string $topic, string $message) {
        $data    = JSON::decode($message);
        $client  = new Client();
        $address = 'http://'.$data['ipAddress'].'/keep-alive';

        if (!array_key_exists($address, $this->_attempts)) {
            $this->_attempts[$address] = self::MAX_ATTEMPTS;
        }

        try {
            $client->get($address);
            $this->_attempts[$address] = self::MAX_ATTEMPTS;
        } catch (Exception $e) {
            $this->_attempts[$address]--;
            $this->_logger->warning('KEEP-ALIVE ERR: '.$address.' (attempts: '.$this->_attempts[$address].')');
        }

        if ($this->_attempts[$address] === 0) {
            $timer = new Timer();
            $timer->setName('device_keep_alive_'.$data['id']);
            $timer->setTimeout('0');

            $this->publish(Topic::TIMER_STOP, JSON::encode($timer));
            unset($this->_attempts[$address]);

            $this->publish(Topic::DEVICE_KEEP_ALIVE_FAIL, $message);
        }
    }

}
