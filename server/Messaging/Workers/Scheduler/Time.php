<?php

namespace SmartHome\Messaging\Workers\Scheduler;

use DI\Container;
use SmartHome\Messaging\Abstracts\AWorker;
use SmartHome\Enum;
use SmartHome\Entity\Timer;
use SmartHome\Common\Utils\JSON;
use SmartHome\Common\Utils\DateTime;
use SmartHome\Service;
use SmartHome\Documents;
use SmartHome\Scheduler;

/**
 * This file defines class for time trigger of task worker.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Time extends AWorker {

    const INTERVAL   = '60sec';
    const TIMER_NAME = 'scheduler_trigger_time';

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
        $this->_setUpSubscriber();
        $this->_establishTimer();
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
        $dayMap = array_values((new Enum\Scheduler\Trigger\Day())->getConstList());

        $dateTime   = new DateTime();
        $hours      = (int)$dateTime->format('H');
        $minutes    = (int)$dateTime->format('i');
        $dayOfWeek  = $dayMap[($dateTime->format('N') - 1)];
        $dayOfMonth = $dateTime->format('j');

        $triggerClass = Documents\Scheduler\Trigger\Time::class;

        $time = ['hours' => $hours, 'minutes' => $minutes];

        $expr = $this->_service->getExpression($triggerClass);

        $expr->addOr(
            $this->_service->getExpression($triggerClass)
                ->field('data.type')->equals(Enum\Scheduler\Trigger\Time::DAILY)
                ->field('data.data.times')->in([$time])
        );
        $expr->addOr(
            $this->_service->getExpression($triggerClass)
                ->field('data.type')->equals(Enum\Scheduler\Trigger\Time::WEEKLY)
                ->field('data.data.'.$dayOfWeek.'.times')->in([$time])
        );
        $expr->addOr(
            $this->_service->getExpression($triggerClass)
                ->field('data.type')->equals(Enum\Scheduler\Trigger\Time::MONTHLY)
                ->field('data.data.days.'.$dayOfMonth)->equals(true)
                ->field('data.data.times')->in([$time])
        );

        $triggers = $this->_service->find($triggerClass, $expr);

        foreach ($triggers as $trigger) { /* @var $trigger Documents\Scheduler\Abstracts\ATrigger */
            $this->_fillDefaultOutput($trigger, $dateTime);

            $scheduler = new Scheduler\Exec($this->_container);
            $trace     = new Scheduler\Trace($this->_container, Scheduler\Trace::getNewId());
            $scheduler->initBy($trigger, $trace);
        }
    }

    /**
     * Fills defaults output by trigger data
     *
     * @param Documents\Scheduler\Abstracts\ATrigger $trigger  Trigger
     * @param DateTime                               $dateTime Datetime
     *
     * @return void
     */
    private function _fillDefaultOutput(Documents\Scheduler\Abstracts\ATrigger $trigger, DateTime $dateTime) {
        $outputs = $trigger->getOutput();
        foreach ($outputs as $output) { /* @var $output Documents\Scheduler\Abstracts\AOutput */
            if ($output->getType() !== Enum\Scheduler\Output\Type::DEFAULTS) {
                continue;
            }

            switch ($output->getKey()) {
                case Enum\Scheduler\Output\Keys::TIME:
                    $output->setValue($dateTime->format('H:i'));
                    break;
            }
        }
    }

    /**
     * Set up subscriber for timer
     *
     * @return void
     */
    private function _setUpSubscriber(): void {
        $topics = [
            Enum\Topic::SCHEDULER_TRIGGER_TIME => [
                'function' => function (string $topic, string $message) {
                    $this->receive($topic, $message);
                },
            ],
        ];
        $this->subscribe($topics);
    }

    /**
     * Establish timer
     *
     * @return void
     */
    private function _establishTimer(): void {
        $timer = new Timer();
        $timer->setName(self::TIMER_NAME);
        $timer->setContent('');
        $timer->setTargetTopic(Enum\Topic::SCHEDULER_TRIGGER_TIME);
        $timer->setTimeout(self::INTERVAL);
        $timer->setRepeated(true);

        $this->publish(Enum\Topic::TIMER_START, JSON::encode($timer));
    }

}
