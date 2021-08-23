<?php

namespace SmartHome\Messaging\Workers\Scheduler;

use DI\Container;
use SmartHome\Messaging\Abstracts\AWorker;
use SmartHome\Common\Utils\JSON;
use SmartHome\Enum;
use SmartHome\Service;
use SmartHome\Documents;
use SmartHome\Scheduler;
use Monolog\Logger;
use JmesPath;
use Throwable;

/**
 * This file defines class for mqtt trigger of task worker.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Mqtt extends AWorker {

    /**
     * Task service
     *
     * @var Service\Task
     */
    private $_service;

    /**
     * Logger
     *
     * @var Logger
     */
    private $_logger;

    /**
     * Container
     *
     * @var Container
     */
    private $_container;

    /**
     * List of registered topics
     *
     * @var array
     */
    private $_registred = [];

    /**
     * Construct method for inject container.
     *
     * @param Container $container Container
     */
    public function __construct(Container $container) {
        parent::__construct($container);

        $this->_service   = $container->get(Service\Task::class);
        $this->_logger    = $container->get('logger');
        $this->_container = $container;

        $this->init();
    }

    /**
     * Prepare worker
     *
     * @return void
     */
    public function prepare() {
        $topics = [
            Enum\Topic::SCHEDULER_TRIGGER_MQTT => [
                'function' => function (string $topic, string $message) {
                    $this->init();
                },
            ],
        ];
        $this->subscribe($topics);
    }

    /**
     * Make initialization of the worker.
     *
     * @return void
     */
    public function init(): void {
        $this->unsubscribe(array_keys($this->_registred));
        $this->_registred = [];

        $this->_service->clear();
        $triggers = $this->_service->find(Documents\Scheduler\Trigger\Mqtt::class);

        foreach ($triggers as $trigger) { /* @var $trigger Documents\Scheduler\Abstracts\ATrigger */
            ['topic' => $triggerTopic] = $trigger->getData();

            if (!$this->_registred[$triggerTopic]) {
                $this->_registred[$triggerTopic] = [
                    'triggers' => [],
                    'function' => function ($topic, $message) use ($triggerTopic) {
                        foreach ($this->_registred[$triggerTopic]['triggers'] as $trigger) {
                            $this->_receive($trigger, $topic, $message);
                        }
                    },
                ];
            }

            $this->_registred[$triggerTopic]['triggers'][] = $trigger;
        }

        foreach ($this->_registred as $topic => $subscriber) {
            $topics[$topic] = [
                'qos'      => 0,
                'function' => function ($topic, $message) use ($subscriber) {
                    $subscriber['function']($topic, $message);
                },
            ];
        }

        if (!empty($topics)) {
            $this->_logger->info('MQTT Scheduler subscribe these topics: "'.join('", "', array_keys($topics)).'".');
            $this->subscribe($topics);
        }
    }

    /**
     * Recieves message for the trigger
     *
     * @param Documents\Scheduler\Abstracts\ATrigger $trigger MQTT trigger
     * @param string                                 $topic   Topic
     * @param string                                 $message Message
     *
     * @return void
     */
    private function _receive(Documents\Scheduler\Abstracts\ATrigger $trigger, $topic, $message): void {
        $this->_fillDefaultOutput($trigger, $topic, $message);

        $scheduler = new Scheduler\Exec($this->_container);
        $trace     = new Scheduler\Trace($this->_container, Scheduler\Trace::getNewId());
        $scheduler->initBy($trigger, $trace);
    }

    /**
     * Fills defaults output by trigger data
     *
     * @param Documents\Scheduler\Abstracts\ATrigger $trigger MQTT trigger
     * @param string                                 $topic   Topic
     * @param string                                 $message Message
     *
     * @return void
     */
    private function _fillDefaultOutput(Documents\Scheduler\Abstracts\ATrigger $trigger, string $topic, string $message): void {
        $outputs = $trigger->getOutput();
        foreach ($outputs as $output) { /* @var $output Documents\Scheduler\Abstracts\AOutput */
            if ($output->getType() !== Enum\Scheduler\Output\Type::DEFAULTS) {
                continue;
            }

            switch ($output->getKey()) {
                case Enum\Scheduler\Output\Keys::TOPIC:
                    $output->setValue($topic);
                    break;
                case Enum\Scheduler\Output\Keys::VALUE:
                    try {
                        ['path' => $path] = $trigger->getData();
                        $data = JSON::decode(($message ?? ''));
                        $value = JmesPath\search((empty($path) ? '*' : $path), $data);
                        $output->setValue(($value) ? $value : $message);
                    } catch (Throwable $t) {
                        $this->_logger->error('Error in Mqtt trigger: '.$t->getMessage());
                        $output->setValue($message);
                    }
                    break;
            }
        }
    }

}
