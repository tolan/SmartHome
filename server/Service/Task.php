<?php

namespace SmartHome\Service;

use MongoDB\BSON\UTCDateTime;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Expr;
use DI\Container;
use SmartHome\Documents\Scheduler;
use SmartHome\Enum;
use SmartHome\Common\Abstracts\Document;
use SmartHome\Common\MQTT;
use SmartHome\Common\Utils\JSON;

/**
 * This file defines service class for task.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Task {

    /**
     * Mongo instance
     *
     * @var DocumentManager
     */
    private $_mongo;

    /**
     * MQTT instance
     *
     * @var MQTT
     */
    private $_mqtt;

    /**
     * Construct method for inject dependencies
     *
     * @param Container $container Container
     */
    public function __construct(Container $container) {
        $this->_mongo = $container->get('mongo');
        $this->_mqtt  = $container->get('mqtt');
    }

    /**
     * Gets expression from query builder
     *
     * @param string $documentClass Classname of target document
     *
     * @return Expr
     */
    public function getExpression(string $documentClass): Expr {
        return $this->_mongo->createQueryBuilder($documentClass)->expr();
    }

    /**
     * Returns tasks for users (optionaly with included shared)
     *
     * @param int  $userId        User ID
     * @param bool $includeShared (optional) Include shared
     *
     * @return Scheduler\Task[]
     */
    public function tasksForUser(int $userId, bool $includeShared = true): array {
        $qb = $this->_mongo->createQueryBuilder(Scheduler\Task::class);
        $qb->addOr($qb->expr()->field('creatorId')->equals($userId));

        if ($includeShared) {
            $qb = $qb->addOr($qb->expr()->field('share')->equals(true));
        }

        $query = $qb->getQuery();
        $query->setRewindable(false);

        $tasks = $query->execute(); /* @var $tasks \Doctrine\ODM\MongoDB\Iterator\UnrewindableIterator */

        return $tasks->toArray();
    }

    /**
     * Finds documents
     *
     * @param string $documentClass Target document class
     * @param Expr   $expression    (optional) Expression
     *
     * @return arrray
     */
    public function find(string $documentClass, Expr $expression = null): array {
        $qb = $this->_mongo->createQueryBuilder($documentClass);
        if ($expression) {
            $qb->addAnd($expression);
        }

        $query = $qb->getQuery();
        $query->setRewindable(false);

        $result = $query->execute(); /* @var $result \Doctrine\ODM\MongoDB\Iterator\UnrewindableIterator */

        return $result->toArray();
    }

    /**
     * Creates a new task
     *
     * @param array $data      Task data with all children
     * @param int   $creatorId User ID of creator
     *
     * @return Scheduler\Task
     */
    public function create(array $data, int $creatorId): Scheduler\Task {
        $task = new Scheduler\Task();
        $task->setCreatorId($creatorId);

        $this->_assignData($task, $data);

        $this->_mongo->persist($task);
        $this->_mongo->flush();

        $this->_mqtt->publish(Enum\Topic::SCHEDULER_TRIGGER_MQTT, JSON::encode($task), 0, 0, true);

        return $task;
    }

    /**
     * Updates an existed task
     *
     * @param array $data Task data with all children
     *
     * @return Scheduler\Task
     */
    public function update(array $data): Scheduler\Task {
        $id = $data['task']['id'];

        $task = $this->_mongo->getRepository(Scheduler\Task::class)->find($id); /* @var $task Scheduler\Task */

        $this->_assignData($task, $data);

        $this->_mongo->persist($task);
        $this->_mongo->flush();

        $this->_mqtt->publish(Enum\Topic::SCHEDULER_TRIGGER_MQTT, JSON::encode($task), 0, 0, true);

        return $task;
    }

    /**
     * Deletes task
     *
     * @param string $id Task ID
     *
     * @return bool
     */
    public function delete($id): bool {
        $task = $this->_mongo->getRepository(Scheduler\Task::class)->find($id); /* @var $task Scheduler\Task */

        if ($task) {
            foreach ($task->getTriggers() as $trigger) { /* @var $trigger Scheduler\Abstracts\ATrigger */
                foreach ($trigger->getConditions() as $condition) {
                    $this->_mongo->remove($condition);
                }

                foreach ($trigger->getOutput() as $output) {
                    $this->_mongo->remove($output);
                }

                $this->_mongo->remove($trigger);
            }

            foreach ($task->getConditions() as $condition) {
                $this->_mongo->remove($condition);
            }

            foreach ($task->getActions() as $action) {
                $this->_mongo->remove($action);
            }

            $this->_mongo->createQueryBuilder()
                ->remove(Scheduler\Log::class)
                ->field('task')->references($task)
                ->getQuery()
                ->execute();

            $this->_mongo->remove($task);
            $this->_mongo->flush();

            $this->_mqtt->publish(Enum\Topic::SCHEDULER_TRIGGER_MQTT, JSON::encode($task), 0, 0, true);
        }

        return true;
    }

    /**
     * Gets logs for task
     *
     * @param string $taskId Task id
     * @param int    $limit  Limit
     * @param int    $skip   Skip
     *
     * @return array
     */
    public function getLogs(string $taskId, int $limit = null, int $skip = null): array {
        $task = $this->_mongo->find(Scheduler\Task::class, $taskId);

        $qb = $this->_mongo->createQueryBuilder(Scheduler\Log::class);
        $qb->field('task')->references($task);
        $qb->sort('created', 'desc');

        if ($limit) {
            $qb->limit($limit);
        }

        if ($skip) {
            $qb->skip($skip);
        }

        $query = $qb->getQuery();
        $query->setRewindable(false);

        $result = $query->execute(); /* @var $result \Doctrine\ODM\MongoDB\Iterator\CachingIterator */

        return $result->toArray();
    }

    /**
     * Gets count of logs for task
     *
     * @param string $taskId Task id
     *
     * @return integer
     */
    public function getLogsCount(string $taskId) {
        $task = $this->_mongo->find(Scheduler\Task::class, $taskId);

        $qb = $this->_mongo->createQueryBuilder(Scheduler\Log::class);
        $qb->field('task')->references($task);
        $qb->count();

        $query = $qb->getQuery();
        $query->setRewindable(false);

        $result = $query->execute();

        return $result;
    }

    /**
     * Creates a new log for task
     *
     * @param Scheduler\Task $task    Task document
     * @param string         $message Message
     *
     * @return Scheduler\Log
     */
    public function createLog(Scheduler\Task $task, string $message): Scheduler\Log {
        $log = new Scheduler\Log();
        $log->setTask($task);
        $log->setCreated(new UTCDateTime());
        $log->setMessage($message);

        $this->_mongo->persist($log);
        $this->_mongo->flush();

        return $log;
    }

    /**
     * Updates document
     *
     * @param Document $document Document
     *
     * @return void
     */
    public function updateDocument(Document $document): void {
        $this->_mongo->persist($document);
        $this->_mongo->flush();
    }

    /**
     * Clears cached documents
     *
     * @return void
     */
    public function clear(): void {
        $this->_mongo->clear();
    }

    /**
     * Assigns data to task
     *
     * @param Scheduler\Task $task Task instance
     * @param array          $data Task data
     *
     * @return void
     */
    private function _assignData(Scheduler\Task $task, array $data): void {
        $task->setName($data['task']['name']);
        $task->setEnabled((bool)$data['task']['enabled']);
        $task->setShare((bool)$data['task']['share']);

        $this->_assignTriggers($task, $data['triggers']);
        $this->_assignConditions($task, $data['conditions']);
        $this->_assignActions($task, $data['actions']);
    }

    /**
     * Assigns triggers to task
     *
     * @param Scheduler\Task $task     Task instance
     * @param array          $triggers Array of triggers data
     *
     * @return void
     */
    private function _assignTriggers(Scheduler\Task $task, array $triggers): void {
        foreach ($task->getTriggers() as $trigger) { /* @var $trigger Scheduler\Abstracts\ATrigger */
            foreach ($trigger->getConditions() as $condition) {
                $this->_mongo->remove($condition);
            }

            foreach ($trigger->getOutput() as $output) {
                $this->_mongo->remove($output);
            }

            $this->_mongo->remove($trigger);
        }

        $task->getTriggers()->clear();
        foreach ($triggers as $trigger) {
            $triggerDoc = Scheduler\Abstracts\ATrigger::createTrigger($trigger['trigger']['type']);
            $triggerDoc->setTask($task);
            $triggerDoc->setData($trigger['trigger']['data']);
            $this->_assignConditions($triggerDoc, $trigger['conditions']);
            $this->_assignOutput($triggerDoc, $trigger['output']);

            $task->getTriggers()->add($triggerDoc);

            $this->_mongo->persist($triggerDoc);
        }
    }

    /**
     * Assigns condition to document
     *
     * @param Scheduler\Task|Scheduler\Abstracts\ATrigger $document   Task or Trigger document instance
     * @param array                                       $conditions Array of conditions
     *
     * @return void
     */
    private function _assignConditions($document, array $conditions): void {
        foreach ($document->getConditions() as $condition) {
            $this->_mongo->remove($condition);
        }

        $document->getConditions()->clear();
        foreach ($conditions as $condition) {
            $conditionDoc = Scheduler\Abstracts\ACondition::createCondition($condition['type']);
            $conditionDoc->setValue($condition['value']);

            $document->getConditions()->add($conditionDoc);

            $this->_mongo->persist($conditionDoc);
        }
    }

    /**
     * Assigns actions to task
     *
     * @param Scheduler\Task $task    Task instance
     * @param array          $actions Array of actions data
     *
     * @return void
     */
    private function _assignActions(Scheduler\Task $task, array $actions) {
        foreach ($task->getActions() as $action) {
            $this->_mongo->remove($action);
        }

        $task->getActions()->clear();
        foreach ($actions as $action) {
            $actionDoc = Scheduler\Abstracts\AAction::createAction($action['type']);
            $actionDoc->setData($action['data']);

            $task->getActions()->add($actionDoc);

            $this->_mongo->persist($actionDoc);
        }
    }

    /**
     * Assigns outpus to trigger
     *
     * @param Scheduler\Abstracts\ATrigger $trigger Trigger instance
     * @param array                        $output  Array of outputs data
     *
     * @return void
     */
    private function _assignOutput(Scheduler\Abstracts\ATrigger $trigger, array $output): void {
        foreach ($trigger->getOutput() as $output) {
            $this->_mongo->remove($output);
        }

        $trigger->getOutput()->clear();
        foreach ($output as $type => $data) {
            foreach ($data as $item) {
                $outputDoc = Scheduler\Abstracts\AOutput::createOutput($type);
                $outputDoc->setKey($item['key']);
                if ($type === Enum\Scheduler\Output\Type::CUSTOM) {
                    $outputDoc->setValue($item['value']);
                }

                $trigger->getOutput()->add($outputDoc);

                $this->_mongo->persist($outputDoc);
            }
        }
    }

}
