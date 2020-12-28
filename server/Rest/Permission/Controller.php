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
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Controller {

    /**
     *
     * @var Service
     */
    private $_commonService;

    /**
     * @var Authorize
     */
    private $_authorize;

    public function __construct (Container $container) {
        $this->_commonService = $container->get(Service::class);
        $this->_authorize = $container->get('authorize');
    }

    public function permissions (Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);

        $query = EntityQuery::create(PermissionEntity::class, []);
        $permissions = $this->_commonService->find($query);

        $data = array_map(function(PermissionEntity $permission) {
            return [
                'permission' => $permission,
            ];
        }, $permissions);

        return $response->withJson($data);
    }

    public function update (Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);
        $data = $request->getParsedBody();

        $query = EntityQuery::create(PermissionEntity::class, [], ['id' => $data['permission']['id']]);
        $perm = $this->_commonService->findOne($query); /* @var $perm PermissionEntity */
        if ($perm) {
            $perm->setName($data['permission']['name']);
            $this->_commonService->persist($perm, true);
        }

        return $response->withStatus(HttpStatusCode::OK);
    }

}
