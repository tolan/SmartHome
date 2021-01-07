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
 * This file defines class for managing process task.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Task {

    // all in seconds
    const INIT_TIMEOUT       = 1;
    const START_TIMEOUT      = 15;
    const STOP_TIMEOUT       = 5;
    const KEEP_ALIVE_TIMEOUT = 30;

    /**
     * Process id
     *
     * @var string
     */
    private $_id;

    /**
     * MQTT client
     *
     * @var MQTT
     */
    private $_mqtt;

    /**
     * Executable command string
     *
     * @var string
     */
    private $_command;

    /**
     * Command parameters
     *
     * @var array
     */
    private $_params = [];

    /**
     * Process state (one of ProcessTaskState)
     *
     * @var string|null
     */
    private $_state = null;

    /**
     * Timestamp of last state message
     *
     * @var float|null
     */
    private $_timestamp = null;

    /**
     * Timeout for active state (in seconds)
     *
     * @var integer
     */
    private $_activeTimeout = 30;

    /**
     * Start time
     *
     * @var float|null
     */
    private $_startTime = null;

    /**
     * Process instance
     *
     * @var Process
     */
    private $_process;

    /**
     * Count of starts
     *
     * @var integer
     */
    private $_starts = 0;

    /**
     * Construct method for define required parameters
     *
     * @param MQTT   $mqtt    MQTT client
     * @param string $command Executable command string
     * @param array  $params  Command parametrs
     * @param string $id      Process id
     */
    public function __construct(MQTT $mqtt, string $command, array $params = [], string $id = null) {
        $this->_id      = ($id) ? $id : uniqid();
        $this->_mqtt    = $mqtt;
        $this->_command = $command;
        $this->_params  = $params;

        $phpBinaryFinder = new PhpExecutableFinder();
        $phpBinaryPath   = $phpBinaryFinder->find();

        $this->_process = new Process(array_merge([$phpBinaryPath, $this->_command, $this->_id], $this->_params));
    }

    /**
     * Returns command
     *
     * @return string
     */
    public function getCommand(): string {
        return $this->_command;
    }

    /**
     * Returns command parameters
     *
     * @return array
     */
    public function getParams(): array {
        return $this->_params;
    }

    /**
     * Returns process state
     *
     * @return string|null
     */
    public function getState(): ?string {
        return $this->_state;
    }

    /**
     * Returns last state message timestamp
     *
     * @return int|null
     */
    public function getTimestamp(): ?int {
        return $this->_timestamp;
    }

    /**
     * Returns process
     *
     * @return Process
     */
    public function getProcess(): Process {
        return $this->_process;
    }

    /**
     * Returns start time
     *
     * @return float|null
     */
    public function getStartTime(): ?float {
        return ($this->_startTime / 1000);
    }

    /**
     * Returns count of start
     *
     * @return int
     */
    public function getStarts(): int {
        return $this->_starts;
    }

    /**
     * Returns timeout for active state
     *
     * @return int
     */
    public function getActiveTimeout(): int {
        return $this->_activeTimeout;
    }

    /**
     * Sets timeout for active state
     *
     * @param int $timeout Timeout
     *
     * @return Task
     */
    public function setActiveTimeout(int $timeout) {
        $this->_activeTimeout = $timeout;

        return $this;
    }

    /**
     * Checks start timeout and throws exception if that is exceeded
     *
     * @return Task
     *
     * @throws Exception
     */
    public function checkStartTimeout() {
        if (!$this->_state && ((microtime(true) - $this->_startTime) > self::START_TIMEOUT)) {
            throw new Exception('Start timeout exceeded.');
        }

        return $this;
    }

    /**
     * Checks active timeout and throws exception if that is exceeded
     *
     * @return Task
     *
     * @throws Exception
     */
    public function checkActiveTimeout() {
        if ($this->_state === ProcessTaskState::ACTIVE && (microtime(true) - $this->_timestamp) > $this->_activeTimeout) {
            throw new Exception('Active timeout exceeded.');
        }

        return $this;
    }

    /**
     * Checks inactive timeout and throws exception if that is exceeded
     *
     * @return Task
     *
     * @throws Exception
     */
    public function checkInactiveTimeout() {
        if ($this->_state === ProcessTaskState::INACTIVE && (microtime(true) - $this->_timestamp) > self::KEEP_ALIVE_TIMEOUT) {
            throw new Exception('Inactive timeout exceeded.');
        }

        return $this;
    }

    /**
     * Checks keep alive timeout and throws exception if that is exceeded
     *
     * @return Task
     *
     * @throws Exception
     */
    public function checkKeepAliveTimeout() {
        if ($this->_state === ProcessTaskState::KEEP_ALIVE && (microtime(true) - $this->_timestamp) > self::KEEP_ALIVE_TIMEOUT) {
            throw new Exception('Keep alive timeout exceeded.');
        }

        return $this;
    }

    /**
     * Starts task process
     *
     * @return Task
     */
    public function start() {
        $this->_startTime = microtime(true);
        $this->_starts++;
        $this->_process->start();

        return $this;
    }

    /**
     * Stops task process
     *
     * @return Task
     */
    public function stop() {
        $this->_startTime = null;
        $this->_state     = null;
        $this->_timestamp = null;
        $this->_process->stop(self::STOP_TIMEOUT);

        return $this;
    }

    /**
     * Initialize task process
     *
     * @return Task
     *
     * @throws Exception Throws when init timeout exceeded
     */
    public function init() {
        $self   = $this;
        $topics = [
            Topic::PROCESS_STATE.'/'.$this->_id => [
                'qos'      => 0,
                'function' => function (string $topic, string $message) use ($self) {
                    $data             = JSON::decode($message);
                    $self->_state     = $data['state'];
                    $self->_timestamp = $data['timestamp'];
                },
            ]
        ];

        $this->_mqtt->subscribe($topics);

        // check channel
        $message = [
            'state'     => ProcessTaskState::INIT,
            'timestamp' => microtime(true),
        ];
        $this->_mqtt->publish(Topic::PROCESS_STATE.'/'.$this->_id, JSON::encode($message));

        $limitInitTime = (microtime(true) + self::INIT_TIMEOUT * 1000 * 1000);
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
