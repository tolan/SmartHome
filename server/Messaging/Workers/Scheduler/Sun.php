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
 * This file defines class for mqtt trigger of task worker.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Sun extends AWorker {

    const INTERVAL   = '60sec';
    const TIMER_NAME = 'scheduler_trigger_sun';

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
     * Config
     *
     * @var array
     */
    private $_config;

    /**
     * Construct method for inject container
     *
     * @param Container $container Container
     */
    public function __construct(Container $container) {
        parent::__construct($container);
        $this->_service   = $container->get(Service\Task::class);
        $this->_container = $container;
        $this->_config    = $container->get('settings');
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
    protected function receive(string $topic, string $message): void {
        ['latitude' => $latitude, 'longitude' => $longitude] = $this->_config['coordinates'];
        $dateTime   = new DateTime();
        $sunData    = date_sun_info($dateTime->getTimestamp(), $latitude, $longitude);

        $triggerClass = Documents\Scheduler\Trigger\Event::class;

        $expr = $this->_service->getExpression($triggerClass);
        $expr->field('data.type')->equals(Enum\Scheduler\Trigger\Event::SUN);

        $events = (new Enum\Scheduler\Trigger\Sun())->getConstList();
        foreach ($events as $event) {
            $eventTime = (new DateTime())->setTimestamp($sunData[$event]);
            $diff      = $eventTime->diff($dateTime);
            $time      = ['hours' => (int)$diff->format('%h'), 'minutes' => (int)$diff->format('%i')];

            $exprEvent = $this->_service->getExpression($triggerClass);
            $exprEvent->field('data.data.type')->equals($event);
            switch (true) {
                case $eventTime->getTimestamp() === $dateTime->getTimestamp():
                    $exprEvent->field('data.data.delayType')->equals(Enum\Scheduler\Trigger\SunDelay::ZERO);
                    break;
                case $eventTime->getTimestamp() > $dateTime->getTimestamp():
                    $exprEvent->field('data.data.delayType')->equals(Enum\Scheduler\Trigger\SunDelay::BEFORE);
                    $exprEvent->field('data.data.times')->in([$time]);
                    break;
                case $eventTime->getTimestamp() < $dateTime->getTimestamp():
                    $exprEvent->field('data.data.delayType')->equals(Enum\Scheduler\Trigger\SunDelay::AFTER);
                    $exprEvent->field('data.data.times')->in([$time]);
                    break;
            }

            $expr->addOr($exprEvent);
        }

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
    private function _fillDefaultOutput(Documents\Scheduler\Abstracts\ATrigger $trigger, DateTime $dateTime): void {
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
            Enum\Topic::SCHEDULER_TRIGGER_SUN => [
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
        $timer->setTargetTopic(Enum\Topic::SCHEDULER_TRIGGER_SUN);
        $timer->setTimeout(self::INTERVAL);
        $timer->setRepeated(true);

        $this->publish(Enum\Topic::TIMER_START, JSON::encode($timer));
    }

}
