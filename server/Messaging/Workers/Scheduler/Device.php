<?php

namespace SmartHome\Messaging\Workers\Scheduler;

use DI\Container;
use SmartHome\Messaging\Abstracts\AWorker;
use SmartHome\Common\Utils\JSON;
use SmartHome\Service;
use SmartHome\Enum;
use SmartHome\Documents;
use SmartHome\Scheduler;

/**
 * This file defines class for device trigger of task worker.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Device extends AWorker {

    /**
     * Task service
     *
     * @var Service\Task
     */
    private $_service;

    /**
     * Container
     *
     * @var Container
     */
    private $_container;

    /**
     * Construct method for inject container
     *
     * @param Container $container Container
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->_service   = $container->get(Service\Task::class);
        $this->_container = $container;
    }

    /**
     * Prepare worker
     *
     * @return void
     */
    protected function prepare(): void {
        $topics = [
            Enum\Topic::DEVICE_CONTROL => [
                'function' => function (string $topic, string $message) {
                    $this->receive($topic, $message);
                },
            ]
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
        $data     = JSON::decode($message);
        ['module' => $module, 'control' => $control, 'traceId' => $traceId] = $data;

        $delay = ($control['controlData']) ? $control['controlData']['delay'] : null;
        if ($delay && $delay['value'] > 0) {
            return void;
        }

        $triggerClass = Documents\Scheduler\Trigger\Event::class;

        $expr = $this->_service->getExpression($triggerClass);

        $expr->field('data.type')->equals(Enum\Scheduler\Trigger\Event::DEVICE);
        $expr->field('data.data.module')->equals($module['id']);
        $expr->field('data.data.action')->equals($control['type']);

        $this->_service->clear();
        $triggers = $this->_service->find($triggerClass, $expr);

        foreach ($triggers as $trigger) { /* @var $trigger Documents\Scheduler\Abstracts\ATrigger */
            $this->_fillDefaultOutput($trigger, $control);

            $scheduler = new Scheduler\Exec($this->_container);
            $trace     = new Scheduler\Trace($this->_container, $traceId);
            $trace->add($module['id']);
            $scheduler->initBy($trigger, $trace);
        }
    }

    /**
     * Fills defaults output with trigger value
     *
     * @param Documents\Scheduler\Abstracts\ATrigger $trigger Trigger
     * @param array                                  $control Control data
     *
     * @return void
     */
    private function _fillDefaultOutput(Documents\Scheduler\Abstracts\ATrigger $trigger, array $control): void {
        $outputs = $trigger->getOutput();
        foreach ($outputs as $output) { /* @var $output Documents\Scheduler\Abstracts\AOutput */
            if ($output->getType() !== Enum\Scheduler\Output\Type::DEFAULTS) {
                continue;
            }

            switch ($output->getKey()) {
                case Enum\Scheduler\Output\Keys::VALUE:
                    $output->setValue($this->_formatControlValue($control));
                    break;
            }
        }
    }

    /**
     * Formats value by control
     *
     * @param array $control Control entity data
     *
     * @return array
     */
    private function _formatControlValue(array $control) {
        $value = null;
        switch ($control['type']) {
            case Enum\ControlType::SWITCH:
                $value = (int)(bool)$control['controlData']['value'];
                break;
            default:
                $value = $control['controlData']['value'];
                break;
        }

        return $value;
    }

}
