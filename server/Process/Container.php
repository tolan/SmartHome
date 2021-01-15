<?php

namespace SmartHome\Process;

use Monolog\Logger;

/**
 * This file defines class for collect tasks
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Container {

    /**
     * Set of registered tasks
     *
     * @var Task[]
     */
    private $_tasks = [];

    /**
     * Logger
     *
     * @var Logger
     */
    private $_logger;

    /**
     * Construct method for inject depencies.
     *
     * @param Logger $logger Logger
     */
    public function __construct(Logger $logger) {
        $this->_logger = $logger;
    }

    /**
     * Adds task
     *
     * @param Task $task Task
     *
     * @return Container
     */
    public function addTask(Task $task) {
        $this->_tasks[] = $task;

        return $this;
    }

    /**
     * Runs and manages assigned tasks
     *
     * @param bool $loop    Execute run in loop
     * @param int  $timeout sleep in microsecond
     *
     * @return boolean
     */
    public function run(bool $loop = false, $timeout = 10000) {
        do {
            foreach ($this->_tasks as $task) {
                if (!$task->getProcess()->isStarted()) {
                    $task->start();
                    continue;
                }

                $error = $task->getProcess()->getIncrementalErrorOutput();
                if ($error) {
                    $this->_logger->error('Process task has failed: '.$error);
                }

                if ($task->getProcess()->getExitCode()) {
                    $message = [
                        'Process task has stopped: '.$task->getCommand().' with params: '.join(', ', $task->getParams()).'.',
                        'and exit code: '.$task->getProcess()->getExitCode()
                    ];
                    $this->_logger->error(join(' ', $message));
                }

                try {
                    $task->checkStartTimeout()->checkActiveTimeout()->checkInactiveTimeout()->checkKeepAliveTimeout();
                } catch (Exception $ex) {
                    $message = [
                        'Restart of task: '.$task->getCommand().' with params: '.join(', ', $task->getParams()).'.',
                        'It is for '.$task->getStarts().' times.',
                        'Reason: '.$ex->getMessage().'.',
                        'Task output: '.$task->getProcess()->getOutput(),
                        'Task error output: '.$task->getProcess()->getErrorOutput(),
                        'Task exit code text: '.$task->getProcess()->getExitCodeText(),
                    ];

                    $this->_logger->warning(join(' ', $message), [$ex]);
                    $task->stop()->start();
                }
            }

            if ($loop && $timeout) {
                usleep($timeout);
            }
        } while ($loop);

        return true;
    }

    /**
     * Stops all tasks
     *
     * @return Container
     */
    public function stopAll() {
        foreach ($this->_tasks as $task) {
            $task->stop();
        }

        return $this;
    }

    /**
     * Returns info about tasks.
     *
     * @return array
     */
    public function getTasksInfo(): array {
        $info = [];
        foreach ($this->_tasks as $task) {
            $info[] = [
                'id'        => $task->getId(),
                'command'   => $task->getCommand(),
                'params'    => $task->getParams(),
                'startTime' => $task->getStartTime(),
                'starts'    => $task->getStarts(),
                'state'     => $task->getState(),
            ];
        }

        return $info;
    }

}
