<?php

namespace SmartHome\Process;

use SmartHome\Common\{
    MQTT,
    Utils\JSON
};
use SmartHome\Enum\{
    ProcessTaskState,
    Topic
};
use Symfony\Component\Process\{
    Process,
    PhpExecutableFinder
};

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Task {

    const INIT_TIMEOUT = 1; // in sec
    const START_TIMEOUT = 15; // in sec
    const STOP_TIMEOUT = 5; // in sec
    const KEEP_ALIVE_TIMEOUT = 30; // in sec

    private $_id;
    private $_mqtt;
    private $_command;
    private $_params = [];
    private $_state = null;
    private $_timestamp = null;
    private $_activeTimeout = 30;
    private $_startTime = null;
    private $_process;
    private $_starts = 0;

    public function __construct (MQTT $mqtt, string $command, array $params = [], string $id = null) {
        $this->_id = $id ? $id : uniqid();
        $this->_mqtt = $mqtt;
        $this->_command = $command;
        $this->_params = $params;

        $phpBinaryFinder = new PhpExecutableFinder();
        $phpBinaryPath = $phpBinaryFinder->find();

        $this->_process = new Process(array_merge([$phpBinaryPath, $this->_command, $this->_id], $this->_params));
    }

    public function getCommand (): string {
        return $this->_command;
    }

    public function getParams (): array {
        return $this->_params;
    }

    public function getState (): ?string {
        return $this->_state;
    }

    public function getTimestamp (): ?int {
        return $this->_timestamp;
    }

    public function getProcess (): Process {
        return $this->_process;
    }

    public function getStartTime (): ?float {
        return $this->_startTime / 1000;
    }

    public function getStarts (): int {
        return $this->_starts;
    }

    public function getActiveTimeout () {
        return $this->_activeTimeout;
    }

    public function setActiveTimeout (int $timeout) {
        $this->_activeTimeout = $timeout;
    }

    public function checkStartTimeout () {
        if (!$this->_state && ((microtime(true) - $this->_startTime) > self::START_TIMEOUT)) {
            throw new Exception('Start timeout exceeded.');
        }

        return $this;
    }

    public function checkActiveTimeout () {
        if ($this->_state === ProcessTaskState::ACTIVE && (microtime(true) - $this->_timestamp) > $this->_activeTimeout) {
            throw new Exception('Active timeout exceeded.');
        }

        return $this;
    }

    public function checkInactiveTimeout () {
        if ($this->_state === ProcessTaskState::INACTIVE && (microtime(true) - $this->_timestamp) > self::KEEP_ALIVE_TIMEOUT) {
            throw new Exception('Inactive timeout exceeded.');
        }

        return $this;
    }

    public function checkKeepAliveTimeout () {
        if ($this->_state === ProcessTaskState::KEEP_ALIVE && (microtime(true) - $this->_timestamp) > self::KEEP_ALIVE_TIMEOUT) {
            throw new Exception('Keep alive timeout exceeded.');
        }

        return $this;
    }

    public function start () {
        $this->_startTime = microtime(true);
        $this->_starts++;
        $this->_process->start();

        return $this;
    }

    public function stop () {
        $this->_startTime = null;
        $this->_state = null;
        $this->_timestamp = null;
        $this->_process->stop(self::STOP_TIMEOUT);

        return $this;
    }

    public function init () {
        $self = $this;
        $topics = [
            Topic::PROCESS_STATE.'/'.$this->_id => [
                'qos' => 0,
                'function' => function (string $topic, string $message) use ($self) {
                    $data = JSON::decode($message);
                    $self->_state = $data['state'];
                    $self->_timestamp = $data['timestamp'];
                },
            ]
        ];

        $this->_mqtt->subscribe($topics);

        // check channel
        $this->_mqtt->publish(Topic::PROCESS_STATE.'/'.$this->_id, JSON::encode([
                    'state' => ProcessTaskState::INIT,
                    'timestamp' => microtime(true),
        ]));

        $limitInitTime = microtime(true) + self::INIT_TIMEOUT * 1000 * 1000;
        do {
            $this->_mqtt->proc(false);
            if (microtime(true) >= $limitInitTime) {
                throw new Exception('Init timeout exceeded.');
            }

            if ($this->_state !== ProcessTaskState::INIT) {
                usleep(100 * 1000);
            }
        } while ($this->_state !== ProcessTaskState::INIT);

        return $this;
    }

}
