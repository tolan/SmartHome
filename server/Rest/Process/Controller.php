<?php

namespace SmartHome\Rest\Process;

use DI\Container;
use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use SmartHome\Enum\HttpStatusCode;
use SmartHome\Authorization\Authorize;
use SmartHome\Enum\{
    Cache,
    Permission
};
use SmartHome\Cache\Storage;
use SmartHome\Common\Utils\JSON;
use SmartHome\Common\MQTT;

/**
 * This file defines class for Process controller
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Controller {

    /**
     * Authorization instance
     *
     * @var Authorize
     */
    private $_authorize;

    /**
     * Cache storage
     *
     * @var Storage
     */
    private $_storage;

    /**
     * MQTT client
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
        $this->_authorize = $container->get('authorize');
        $this->_storage   = $container->get('cache')->getCache(Cache::SCOPE_PROCESS);
        $this->_mqtt      = $container->get('mqtt');
    }

    /**
     * Gets list of processes
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function processes(Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);

        $data = array_map(function($process) use($time) {
            $process['runningTime'] = (microtime(true) - ($process['startTime'] * 1000));

            $data = [
                'process' => $process,
            ];
            return $data;
        }, JSON::decode($this->_storage->get('statusInfo')));

        return $response->withJson($data);
    }

    /**
     * Restarts process
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function restart(Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);
        $data = $request->getParsedBody();

        $processId = $data['process']['id'];

        Helper\Process::sendRestart($this->_mqtt, $processId);

        return $response->withStatus(HttpStatusCode::OK);
    }

}
