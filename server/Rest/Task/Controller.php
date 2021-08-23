<?php

namespace SmartHome\Rest\Task;

use DI\Container;
use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use SmartHome\Authorization\Authorize;
use SmartHome\Service;
use Exception;
use SmartHome\Enum\{
    HttpStatusCode,
    Notification as NotificationType,
    Permission
};
use SmartHome\Common\Notification;
use Monolog\Logger;
use SmartHome\Service\User as UserService;
use SmartHome\Documents\Scheduler;
use SmartHome\Enum;

/**
 * This file defines class for Task controller
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Controller {

    /**
     * Authoriztaion instance
     *
     * @var Authorize
     */
    private $_authorize;

    /**
     * Task service
     *
     * @var Service\Task
     */
    private $_service;

    /**
     * Logger instance
     *
     * @var Logger
     */
    private $_logger;

    /**
     * User service
     *
     * @var UserService
     */
    private $_userService;

    /**
     * Construct method for inject dependencies
     *
     * @param Container $container Container
     */
    public function __construct(Container $container) {
        $this->_authorize   = $container->get('authorize');
        $this->_service     = $container->get(Service\Task::class);
        $this->_logger      = $container->get('logger');
        $this->_userService = $container->get(UserService::class);
    }

    /**
     * Gets list of tasks
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function tasks(Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_SCHEDULER]);

        $user  = $this->_userService->getCurrentUser();
        $tasks = $this->_service->tasksForUser($user['user']->getId(), true);

        $data = array_map(function (Scheduler\Task $task) {
            return $this->_transformTask($task);
        }, $tasks);

        return $response->withJson($data);
    }

    /**
     * Creates task
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function create(Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_SCHEDULER]);
        $user = $this->_userService->getCurrentUser();

        try {
            $task     = $this->_service->create($request->getParsedBody(), $user['user']->getId());
            $data     = $this->_transformTask($task);
            $response = $response->withJson($data, HttpStatusCode::OK);
        } catch (Exception $ex) {
            $response = $response->withStatus(HttpStatusCode::INTERNAL_SERVER_ERROR);
            $response = Notification::withResponse($response, NotificationType::INVALID_REQUEST_DATA);
            $this->_logger->error('Task create has failed: '.$ex->getMessage(), [$ex]);
        }

        return $response;
    }

    /**
     * Updates task
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function update(Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_SCHEDULER]);

        try {
            $task     = $this->_service->update($request->getParsedBody());
            $data     = $this->_transformTask($task);
            $response = $response->withJson($data, HttpStatusCode::OK);
        } catch (Exception $ex) {
            $response = $response->withStatus(HttpStatusCode::INTERNAL_SERVER_ERROR);
            $response = Notification::withResponse($response, NotificationType::INVALID_REQUEST_DATA);
            $this->_logger->error('Task update has failed: '.$ex->getMessage(), [$ex]);
        }

        return $response;
    }

    /**
     * Deletes task
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param array    $params   Parameters (id)
     *
     * @return Response
     */
    public function delete(Request $request, Response $response, array $params) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_SCHEDULER]);

        try {
            $id       = $params['id'];
            $this->_service->delete($id);
            $response = $response->withStatus(HttpStatusCode::OK);
        } catch (Exception $ex) {
            $response = $response->withStatus(HttpStatusCode::INTERNAL_SERVER_ERROR);
            $response = Notification::withResponse($response, NotificationType::INVALID_REQUEST_DATA);
            $this->_logger->error('Task delete has failed: '.$ex->getMessage(), [$ex]);
        }

        return $response;
    }

    /**
     * Gets logs for task
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function logs(Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_SCHEDULER]);

        $taskId = $request->getParam('taskId');
        $limit  = $request->getParam('limit');
        $skip   = $request->getParam('skip');

        $result = [
            'logs' => array_map(function (Scheduler\Log $log) {
                return ['log' => $log];
            }, $this->_service->getLogs($taskId, $limit, $skip)),
            'count' => $this->_service->getLogsCount($taskId),
        ];

        return $response->withJson($result);
    }

    /**
     * Transforms task for API
     *
     * @param Scheduler\Task $task Task
     *
     * @return array
     */
    private function _transformTask(Scheduler\Task $task): array {
        return [
            'task'     => $task,
            'triggers' => array_map(function(Scheduler\Abstracts\ATrigger $trigger) {
                return $this->_tranformTrigger($trigger);
            }, $task->getTriggers()->toArray()),
            'conditions' => $task->getConditions()->toArray(),
            'actions'    => $task->getActions()->toArray(),
        ];
    }

    /**
     * Transforms trigger for API
     *
     * @param Scheduler\Abstracts\ATrigger $trigger Trigger
     *
     * @return array
     */
    private function _tranformTrigger(Scheduler\Abstracts\ATrigger $trigger): array {
        $output = array_reduce($trigger->getOutput()->toArray(), function($acc, Scheduler\Abstracts\AOutput $item) {
            switch ($item->getType()) {
                case Enum\Scheduler\Output\Type::DEFAULTS:
                    $acc[Enum\Scheduler\Output\Type::DEFAULTS][] = $item;
                    break;
                case Enum\Scheduler\Output\Type::CUSTOM:
                    $acc[Enum\Scheduler\Output\Type::CUSTOM][]   = $item;
                    break;
            }

            return $acc;
        }, [
            Enum\Scheduler\Output\Type::DEFAULTS => [],
            Enum\Scheduler\Output\Type::CUSTOM   => []
        ]);

        return [
            'trigger'    => $trigger,
            'conditions' => $trigger->getConditions()->toArray(),
            'output'     => $output,
        ];
    }

}
