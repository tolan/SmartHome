<?php

namespace SmartHome\Process;

use Monolog\Logger;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Container {

    /**
     *
     * @var Task[]
     */
    private $_tasks = [];

    /**
     *
     * @var Logger
     */
    private $_logger;

    public function __construct (Logger $logger) {
        $this->_logger = $logger;
    }

    public function addTask (Task $task) {
        $this->_tasks[] = $task;
    }

    public function run ($loop = false, $timeout = 10000) {
        do {
            foreach ($this->_tasks as $task) {
                if (!$task->getProcess()->isStarted()) {
                    $task->start();
                    continue;
                }

                if (($error = $task->getProcess()->getIncrementalErrorOutput())) {
                    $this->_logger->error('Process task has failed: '.$error);
                }

                if ($task->getProcess()->getExitCode()) {
                    $this->_logger->error(
                            'Process task has stopped: '.$task->getCommand().' with params: '.join(', ', $task->getParams()).'. '.
                            'and exit code: '.$task->getProcess()->getExitCode()
                    );
                }

                try {
                    $task->checkStartTimeout()
                            ->checkActiveTimeout()
                            ->checkInactiveTimeout()
                            ->checkKeepAliveTimeout();
                } catch (Exception $ex) {
                    $this->_logger->warning(
                            'Restart of task: '.$task->getCommand().' with params: '.join(', ', $task->getParams()).'. '.
                            'It is for '.$task->getStarts().' times. '.
                            'Reason: '.$ex->getMessage().'. '.
                            'Task output: '.$task->getProcess()->getOutput().' '.
                            'Task error output: '.$task->getProcess()->getErrorOutput().' '.
                            'Task exit code text: '.$task->getProcess()->getExitCodeText().' '
                            , [$ex]
                    );
                    $task->stop()->start();
                }
            }

            if ($loop && $timeout) {
                usleep($timeout);
            }
        } while ($loop);

        return true;
    }

    public function stopAll () {
        foreach ($this->_tasks as $task) {
            $task->stop();
        }

        return $this;
    }

}
