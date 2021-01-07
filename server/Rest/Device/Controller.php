<?php

namespace SmartHome\Rest\Device;

use SmartHome\Common\{
    MQTT,
    Utils\JSON
};
use DI\Container;
use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use SmartHome\Entity\{
    User,
    Group,
    Permission,
    Device,
    Firmware,
    Room,
    Module,
    Control
};
use SmartHome\Enum\{
    HttpStatusCode,
    Permission as PermissionType,
    ControlType
};
use SmartHome\Rest\Device\Helper\{
    Registration,
    Control as ControlHelper,
    Firmware as FirmwareUpdate,
    Restart
};
use SmartHome\Authorization\Authorize;
use SmartHome\Database\{
    EntityQuery,
    RelationQuery
};
use SmartHome\Common\Service;
use SmartHome\Service\Device as DeviceService;
use SmartHome\Service\User as UserService;

/**
 * This file defines class for Device controller
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
     * Device service
     *
     * @var DeviceService
     */
    private $_deviceService;

    /**
     * User service
     *
     * @var UserService
     */
    private $_userService;

    /**
     * MQTT client
     *
     * @var MQTT
     */
    private $_mqtt;

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
        $this->_mqtt          = $container->get('mqtt');
        $this->_authorize     = $container->get('authorize');
        $this->_commonService = $container->get(Service::class);
        $this->_deviceService = $container->get(DeviceService::class);
        $this->_userService   = $container->get(UserService::class);
    }

    /**
     * Gets list of devices
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function list(Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [PermissionType::TYPE_SECTION_DEVICES]);

        $data = $this->_deviceService->getDevices();

        return $response->withJson($data);
    }

    /**
     * Updates device
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function update(Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [PermissionType::TYPE_SECTION_ADMIN]);
        $data = $request->getParsedBody();
        if (!$data['device'] || !$data['device']['id'] || !$data['firmware'] || !$data['firmware']['id']) {
            $response = $response->withStatus(HttpStatusCode::BAD_REQUEST);
        }

        $devceQuery    = EntityQuery::create(Device::class, [[Firmware::class], [Room::class]], ['id' => $data['device']['id']]);
        $device        = $this->_commonService->findOne($devceQuery); /* @var $device Device */
        $firmwareQuery = EntityQuery::create(Firmware::class, [], ['id' => $data['firmware']['id']]);
        $firmware      = $this->_commonService->findOne($firmwareQuery); /* @var $firmware Firmware */

        if ($device && $firmware) {
            $originFirmare = $device->getFirmware();
            if ($originFirmare) {
                $originFirmare->getDevices()->removeElement($device);
                $this->_commonService->persist($originFirmare);
            }

            $uploadFirmware = false;
            if (!$originFirmare || $originFirmare->getId() !== $firmware->getId()) {
                $uploadFirmware = true;
            }

            $device->setName($data['device']['name']);
            $device->setFirmware($firmware);
            $firmware->getDevices()->add($device);

            $originRoom = $device->getRoom();
            if ($originRoom) {
                $originRoom->getDevices()->removeElement($device);
                $device->setRoom(null);
                $this->_commonService->persist($originRoom);
            }

            if ($data['room']) {
                $roomQuery = EntityQuery::create(Room::class, [], ['id' => $data['room']['id']]);
                $room      = $this->_commonService->findOne($roomQuery); /* @var $room Room */

                $device->setRoom($room);
                $room->getDevices()->add($device);
                $this->_commonService->persist($room);
            }

            $controlsToUpdate = [];
            if (!empty($data['modules'])) {
                $device->getModules()->clear();
                foreach ($data['modules'] as $moduleData) {
                    if ($moduleData['module']['id']) {
                        $moduleQuery = EntityQuery::create(Module::class, [[Control::class]], ['id' => $moduleData['module']['id']]);
                        $module      = $this->_commonService->findOne($moduleQuery); /* @var $room Room */
                        $module->setName($moduleData['module']['name'])->setSettingsData($moduleData['module']['settingsData']);
                        foreach ($module->getControls()->toArray() as $control) {
                            $this->_commonService->remove($control);
                        }

                        $module->getControls()->clear();
                        $this->_commonService->persist($module);
                    } else {
                        $module = new Module();
                        $module->setName($moduleData['module']['name']);
                        $module->setSettingsData($moduleData['module']['settingsData']);
                        $module->setDevice($device);
                        $this->_commonService->persist($module);
                    }

                    $device->getModules()->add($module);

                    if (!empty($moduleData['controls'])) {
                        foreach ($moduleData['controls'] as $controlData) {
                            $control = new Control();
                            $control->setType($controlData['control']['type']);
                            $control->setModule($module);
                            $control->setControlData($controlData['control']['controlData']);
                            $module->getControls()->add($control);
                            $this->_commonService->persist($control);
                            if ($control->getType() === ControlType::MQTT) {
                                $controlsToUpdate[] = [
                                    'module'  => $module,
                                    'control' => $control,
                                ];
                            }
                        }
                    }

                    $this->_commonService->persist($module);
                    $device->getModules()->add($module);
                }
            }

            $this->_commonService->persist($device, true);

            foreach ($controlsToUpdate as $control) {
                ControlHelper::sendControlUpdate($this->_mqtt, $device, $control['module'], $control['control']);
            }

            if ($uploadFirmware) {
                FirmwareUpdate::sendFirmwareUpdate($device, $this->_mqtt);
            }

            $response = $response->withStatus(HttpStatusCode::OK);
        } else {
            $response = $response->withStatus(HttpStatusCode::NOT_FOUND);
        }

        return $response;
    }

    /**
     * Deletes device
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function delete(Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [PermissionType::TYPE_SECTION_ADMIN]);

        $query  = EntityQuery::create(Device::class, [[Module::class, Control::class]], ['id' => $request->getParsedBodyParam('id')]);
        $device = $this->_commonService->findOne($query); /* @var $device Device */
        if ($device) {
            foreach ($device->getModules()->toArray() as $module) { /* @var $module Module */
                foreach ($module->getControls()->toArray() as $control) {
                    $this->_commonService->remove($control);
                }

                $this->_commonService->remove($module);
            }

            $this->_commonService->remove($device, true);

            ControlHelper::sendControlUpdate($this->_mqtt, $device);

            $response = $response->withStatus(HttpStatusCode::OK);
        } else {
            $response = $response->withStatus(HttpStatusCode::NOT_FOUND);
        }

        return $response;
    }

    /**
     * Gets list of controlled devices
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function controlled(Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [PermissionType::TYPE_SECTION_DEVICES]);
        $data = [];
        $user = $this->_userService->getCurrentUser();
        if ($user) {
            $permissionQuery = (new RelationQuery(Permission::class))->conditions(['type' => PermissionType::TYPE_DEVICE_CONTROL]);
            $groupQuery      = (new RelationQuery(Group::class))->with($permissionQuery);
            $userQuery       = (new EntityQuery(User::class))->with($groupQuery)->conditions(['id' => $user['user']->getId()]);
            $user            = $this->_commonService->findOne($userQuery); /* @var $user User */

            $map = function (Group $group) {
                return $group->getId();
            };
            $groupsIds = $user->getGroups()->map($map)->toArray();

            $groupQuery  = (new RelationQuery(Group::class))->conditions(['id' => $groupsIds]);
            $roomQuery   = (new RelationQuery(Room::class))->with($groupQuery);
            $moduleQuery = (new RelationQuery(Module::class))->with(new RelationQuery(Control::class));
            $deviceQuery = (new EntityQuery(Device::class))->with($roomQuery)->with($moduleQuery)->with(new RelationQuery(Firmware::class));

            $devices = $this->_commonService->find($deviceQuery);

            foreach ($devices as $device) {
                $data[] = $this->_transformDevice($device);
            }
        }

        return $response->withJson($data);
    }

    /**
     * Gets current state of modules
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function modules(Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [PermissionType::TYPE_SECTION_DEVICES]);

        $modulesIds = array_column($request->getParsedBodyParam('modules'), 'id');
        $modules    = $this->_deviceService->getModules($modulesIds);

        $data = array_map(function (Module $module) {
            return $this->_transformModule($module);
        }, $modules);

        return $response->withJson($data);
    }

    /**
     * Controls device
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function control(Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [PermissionType::TYPE_DEVICE_CONTROL]);

        $device  = $this->_commonService->findOne(EntityQuery::create(Device::class, [], ['id' => $request->getParsedBodyParam('device')['id']])); /* @var $device Device */
        $module  = $this->_commonService->findOne(EntityQuery::create(Module::class, [], ['id' => $request->getParsedBodyParam('module')['id']])); /* @var $module Module */
        $control = $this->_commonService->findOne(EntityQuery::create(Control::class, [], ['id' => $request->getParsedBodyParam('control')['id']])); /* @var $control Control */

        $controlData = $request->getParsedBodyParam('control')['controlData'];

        if ($device && $module && $control && $controlData) {
            $control->setControlData($controlData);
            $this->_commonService->persist($control, true);

            ControlHelper::syncControls($this->_commonService, $device, $module, $control);
            ControlHelper::sendControlUpdate($this->_mqtt, $device, $module, $control);

            $response = $response->withStatus(HttpStatusCode::OK);
        } else {
            $response = $response->withStatus(HttpStatusCode::NOT_FOUND);
        }

        return $response;
    }

    /**
     * Restarts device
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function restart(Request $request, Response $response) {
        $deviceId = $request->getAttribute('id');

        $query  = EntityQuery::create(Device::class, [], ['id' => $deviceId]);
        $device = $this->_commonService->findOne($query); /* @var $device Device */

        if ($device) {
            $response = $response->withStatus(HttpStatusCode::OK);
            Restart::send($this->_mqtt, $device);
        } else {
            $response = $response->withStatus(HttpStatusCode::NOT_FOUND);
        }

        return $response;
    }

    /**
     * Registration of device
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function register(Request $request, Response $response) {
        $device = new Device();
        $device->setMac($request->getParsedBodyParam('mac'));
        $device->setIpAddress($request->getServerParam('REMOTE_ADDR'));

        Registration::sendRegistration($device, $this->_mqtt);

        return $response->withStatus(HttpStatusCode::OK);
    }

    /**
     * Remote controls
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param array    $params   Parameters (id, action, value)
     *
     * @return Response
     */
    public function remoteControl(Request $request, Response $response, array $params) {
        $this->_authorize->checkApiToken($request);
        ['id' => $moduleId, 'action' => $action, 'value' => $value] = $params;

        $data = [];
        if ($request->getParam('data')) {
            try {
                $data = JSON::decode($request->getParam('data'));
            } catch (\Exception $ex) {
                return $response->withStatus(HttpStatusCode::BAD_REQUEST)->withJson('Invalid additional data.');
            }
        }

        ControlHelper::sendRemoteControl($this->_commonService, $this->_mqtt, $moduleId, $action, $value, $data);

        return $response->withStatus(HttpStatusCode::OK)->withJson('OK');
    }

    /**
     * Transforms device to output
     *
     * @param Device $device Device
     *
     * @return array
     */
    private function _transformDevice(Device $device) {
        $modules = [];
        foreach ($device->getModules()->toArray() as $module) {
            $modules[] = $this->_transformModule($module);
        }

        return [
            'device'   => $device,
            'firmware' => $device->getFirmware(),
            'room'     => $device->getRoom(),
            'modules'  => $modules,
        ];
    }

    /**
     * Transforms module
     *
     * @param Module $module Module
     *
     * @return array
     */
    private function _transformModule(Module $module) {
        $controls = array_map(function(Control $control) {
            return ['control' => $control];
        }, $module->getControls()->toArray());

        return [
            'module'   => $module,
            'controls' => $controls,
        ];
    }

}
