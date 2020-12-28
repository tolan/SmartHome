<?php

namespace SmartHome\Rest\Room;

use DI\Container;
use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use SmartHome\Entity\{
    Room,
    Device,
    Group
};
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

    public function rooms (Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_DEVICES, Permission::TYPE_SECTION_ADMIN], false);

        $query = EntityQuery::create(Room::class, [[Device::class], [Group::class]]);
        $rooms = $this->_commonService->find($query);

        $data = array_map(function(Room $room) {
            return [
                'room' => $room,
                'devices' => $room->getDevices()->toArray(),
                'groups' => $room->getGroups()->toArray(),
            ];
        }, $rooms);

        return $response->withJson($data);
    }

    public function create (Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);
        $data = $request->getParsedBody();

        $room = new Room();
        $room->setName($data['room']['name']);
        $this->_commonService->persist($room);

        $query = EntityQuery::create(Device::class, [], ['id' => array_column($data['devices'], 'id')]);
        $devices = $this->_commonService->find($query);
        $this->_commonService->assembleRelationsManyToOne($room, Device::class, $devices);

        $query = EntityQuery::create(Group::class, [], ['id' => array_column($data['groups'], 'id')]);
        $groups = $this->_commonService->find($query);
        $this->_commonService->assembleRelationsManyToMany($room, Group::class, $groups);

        $this->_commonService->persist($room, true);

        return $response->withStatus(HttpStatusCode::OK);
    }

    public function update (Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);
        $data = $request->getParsedBody();

        $query = EntityQuery::create(Room::class, [], ['id' => $data['room']['id']]);
        $room = $this->_commonService->findOne($query); /* @var $room Room */
        if ($room) {
            $room->setName($data['room']['name']);
            $this->_commonService->persist($room);

            $query = EntityQuery::create(Device::class, [], ['id' => array_column($data['devices'], 'id')]);
            $devices = $this->_commonService->find($query);
            $this->_commonService->assembleRelationsManyToOne($room, Device::class, $devices);

            $query = EntityQuery::create(Group::class, [], ['id' => array_column($data['groups'], 'id')]);
            $groups = $this->_commonService->find($query);
            $this->_commonService->assembleRelationsManyToMany($room, Group::class, $groups, true);
        }

        return $response->withStatus(HttpStatusCode::OK);
    }

    public function delete (Request $request, Response $response, array $params) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);

        $query = EntityQuery::create(Room::class, [], ['id' => $params['id']]);
        $room = $this->_commonService->findOne($query); /* @var $room Room */

        if ($room) {
            $this->_commonService->remove($room, true);
        }

        return $response->withStatus(HttpStatusCode::OK);
    }

}
