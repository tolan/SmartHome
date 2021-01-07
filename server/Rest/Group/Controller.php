<?php

namespace SmartHome\Rest\Group;

use DI\Container;
use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use SmartHome\Entity\{
    Group,
    Permission as PermissionEntity,
    Room
};
use SmartHome\Enum\{
    HttpStatusCode,
    Permission
};
use SmartHome\Authorization\Authorize;
use SmartHome\Database\EntityQuery;
use SmartHome\Common\Service;

/**
 * This file defines class for Group controller.
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
     * Gets list of groups
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function groups(Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);

        $query  = EntityQuery::create(Group::class, [[PermissionEntity::class], [Room::class]]);
        $groups = $this->_commonService->find($query);

        $data = array_map(function(Group $group) {
            $data = [
                'group'       => $group,
                'permissions' => $group->getPermissions()->toArray(),
                'rooms'       => $group->getRooms()->toArray(),
            ];
            return $data;
        }, $groups);

        return $response->withJson($data);
    }

    /**
     * Creates group
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function create(Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);
        $data = $request->getParsedBody();

        $group = new Group();
        $group->setName($data['group']['name']);
        $this->_commonService->persist($group);

        $query = EntityQuery::create(PermissionEntity::class, [], ['id' => array_column($data['permissions'], 'id')]);
        $perms = $this->_commonService->find($query);
        $this->_commonService->assembleRelationsManyToMany($group, PermissionEntity::class, $perms);

        $query = EntityQuery::create(Room::class, [], ['id' => array_column($data['rooms'], 'id')]);
        $rooms = $this->_commonService->find($query);
        $this->_commonService->assembleRelationsManyToMany($group, Room::class, $rooms, true);

        return $response->withStatus(HttpStatusCode::OK);
    }

    /**
     * Updates group
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function update(Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);
        $data = $request->getParsedBody();

        $query = EntityQuery::create(Group::class, [], ['id' => $data['group']['id']]);
        $group = $this->_commonService->findOne($query); /* @var $group Group */

        if ($group) {
            $group->setName($data['group']['name']);

            $this->_commonService->persist($group);

            $query = EntityQuery::create(PermissionEntity::class, [], ['id' => array_column($data['permissions'], 'id')]);
            $perms = $this->_commonService->find($query);
            $this->_commonService->assembleRelationsManyToMany($group, PermissionEntity::class, $perms);

            $query = EntityQuery::create(Room::class, [], ['id' => array_column($data['rooms'], 'id')]);
            $rooms = $this->_commonService->find($query);
            $this->_commonService->assembleRelationsManyToMany($group, Room::class, $rooms, true);
        }

        return $response->withStatus(HttpStatusCode::OK);
    }

    /**
     * Deletes group
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param array    $params   Parameters (id)
     *
     * @return Response
     */
    public function delete(Request $request, Response $response, array $params) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);

        $query = EntityQuery::create(Group::class, [], ['id' => $params['id']]);
        $group = $this->_commonService->findOne($query); /* @var $group Group */

        if ($group) {
            $this->_commonService->remove($group, true);
        }

        return $response->withStatus(HttpStatusCode::OK);
    }

}
