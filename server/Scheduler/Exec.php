<?php

namespace SmartHome\Scheduler;

use MongoDB\BSON\UTCDateTime;
use DI\Container;
use SmartHome\Common\Abstracts\Document;
use SmartHome\Documents\Scheduler\Abstracts\{
    ATrigger,
    AOutput,
    AAction,
    ACondition
};
use SmartHome\Service;

/**
 * This file defines class for execution of scheduler task.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Exec {

    /**
     * Container instance
     *
     * @var Container
     */
    private $_container;

    /**
     * Task service
     *
     * @var Service\Task;
     */
    private $_service;

    /**
     * Construct method for inject dependencies
     *
     * @param Container $container Container
     */
    public function __construct(Container $container) {
        $this->_container = $container;
        $this->_service   = $container->get(Service\Task::class);
    }

    /**
     * Starts execution of the task
     *
     * @param ATrigger $trigger Trigger
     * @param Trace    $trace   Trace
     *
     * @return void
     */
    public function initBy(ATrigger $trigger, Trace $trace): void {
        $task = $trigger->getTask();

        if ($task->getEnabled()) {
            $this->_service->createLog($task, 'Start spouštěčem "'.$trigger->getMessage().'".');

            $context = new Context();
            $context->setContainer($this->_container);
            $context->setDocument($trigger);
            $context->setDate(new UTCDateTime());
            $context->setTask($task);
            $context->setTrace($trace);

            foreach ($trigger->getOutput()->toArray() as $output) { /* @var $output AOutput */
                $context->addOutput($output);
            }

            if ($this->_evaluateConditions($trigger->getConditions()->toArray(), $context)) {
                $this->_service->createLog($task, 'Všechny podmínky špouštěče vyhovují.');
                $context->setDocument($task);
                if ($this->_evaluateConditions($task->getConditions()->toArray(), $context)) {
                    $this->_service->createLog($task, 'Všechny obecné podmínky vyhovují.');
                    $this->_updateLastRun($trigger, $context);
                    $this->_updateLastRun($task, $context);

                    $context->setDocument($trigger);
                    $this->_performActions($task->getActions()->toArray(), $context);
                    $this->_service->createLog($task, 'Všechny akce byly vykonány.');
                }
            }

            $this->_service->clear();
        }
    }

    /**
     * Updates last run of the document
     *
     * @param Document $document Task or Trigger document
     * @param Context  $context  Context
     *
     * @return void
     */
    private function _updateLastRun(Document $document, Context $context): void {
        $document->setLastRun($context->getDate());
        $this->_service->updateDocument($document);
    }

    /**
     * Evaluates conditions
     *
     * @param ACondition[] $conditions Conditions
     * @param Context      $context    Context
     *
     * @return bool
     */
    private function _evaluateConditions(array $conditions, Context $context): bool {
        $condition = new Condition($conditions, $context);
        $isValid   = $condition->isValid();

        if (!$isValid) {
            $prefix = ($context->getDocument() instanceof ATrigger) ? 'Podmínky spouštěče: ' : 'Obecná podmínka: ';
            $this->_service->createLog($context->getTask(), $prefix.$condition->getErrorMessage());
        }

        return $isValid;
    }

    /**
     * Performs actions
     *
     * @param AAction[] $actions Actions
     * @param Context   $context Context
     *
     * @return void
     */
    private function _performActions(array $actions, Context $context): void {
        $action = new Actions($actions, $context);
        if ($action->isExecutable()) {
            $action->execute();
        } else {
            $this->_service->createLog($context->getTask(), 'Vykonání akcí bylo zastaveno pro cyklickou závislost.');
        }
    }

}
