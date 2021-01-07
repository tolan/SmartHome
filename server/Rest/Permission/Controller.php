<?php

namespace SmartHome\Rest\Permission;

use DI\Container;
use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use SmartHome\Entity\Permission as PermissionEntity;
use SmartHome\Enum\HttpStatusCode;
use SmartHome\Authorization\Authorize;
use SmartHome\Enum\Permission;
use SmartHome\Database\EntityQuery;
use SmartHome\Common\Service;

/**
 * This file defines class for Permission controller
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Controller {

    /**
     * Common service
     *
     * @var Service
     */
    private $_commonService;

    /**
     * Authorization instance
     *
     * @var Authorize
     */
    private $_authorize;

    /**
     * Construct method for inject dependencies
     *
     * @param Container $container Container
     */
    public function __construct(Container $container) {
        $this->_commonService = $container->get(Service::class);
        $this->_authorize     = $container->get('authorize');
    }

    /**
     * Gets list of permissions
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function permissions(Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);

        $query       = EntityQuery::create(PermissionEntity::class, []);
        $permissions = $this->_commonService->find($query);

        $data = array_map(function(PermissionEntity $permission) {
            $data = [
                'permission' => $permission,
            ];
            return $data;
        }, $permissions);

        return $response->withJson($data);
    }

    /**
     * Updates permission
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function update(Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);
        $data = $request->getParsedBody();

        $query = EntityQuery::create(PermissionEntity::class, [], ['id' => $data['permission']['id']]);
        $perm  = $this->_commonService->findOne($query); /* @var $perm PermissionEntity */
        if ($perm) {
            $perm->setName($data['permission']['name']);
            $this->_commonService->persist($perm, true);
        }

        return $response->withStatus(HttpStatusCode::OK);
    }

}
