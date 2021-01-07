<?php

namespace SmartHome\Service;

use SmartHome\Enum;
use SmartHome\Cache;
use SmartHome\Common\Service;
use SmartHome\Entity\{
    Device as DeviceEntity,
    Module,
    Control,
    Firmware,
    Room,
    Timer
};
use SmartHome\Database\EntityQuery;
use SmartHome\Event\Abstracts\AMessage;
use DI\Container;

/**
 * This file defines class for Device service.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Device {

    /**
     * Common service
     *
     * @var Service
     */
    private $_service;

    /**
     * Cache storage
     *
     * @var Cache\Storage;
     */
    private $_cache;

    /**
     * Construct method for inject dependencies
     *
     * @param Container $container Container
     */
    public function __construct(Container $container) {
        $this->_service = $container->get(Service::class);
        $this->_cache   = $container->get('cache')->getCache(Enum\Cache::SCOPE_DEVICE);
    }

    /**
     * Receives update message
     *
     * @param AMessage $message Message
     *
     * @return void
     */
    public function receive(AMessage $message) {
        if (!($message->getData() instanceof Timer)) {
            $this->_cache->delete('*');
        }
    }

    /**
     * Gets all devices for rest api
     *
     * @return array
     */
    public function getDevices() {
        $query   = EntityQuery::create(DeviceEntity::class, [[Module::class, Control::class], [Firmware::class], [Room::class]]);
        $devices = $this->_service->find($query);

        return array_map(function (DeviceEntity $device) {
            return $this->_transformDevice($device);
        }, $devices);
    }

    /**
     * Gets modules by given id for rest api
     *
     * @param array $ids Modules ids
     *
     * @return array
     */
    public function getModules(array $ids = []) {
        $modules = [];
        foreach ($this->_cache->getMultiple($ids) as $id => $entity) {
            if ($entity) {
                $modules[$id] = unserialize($entity);
            }
        }

        $rest = array_diff($ids, array_keys($modules));
        if ($rest) {
            $query = EntityQuery::create(Module::class, [[Control::class]], ['id' => $rest]);
            foreach ($this->_service->find($query) as $module) { /* @var $module Module */
                $modules[$module->getId()] = $module;
                $this->_cache->set($module->getId(), serialize($module));
            }
        }

        ksort($modules);

        return array_values($modules);
    }

    /**
     * Transforms device
     *
     * @param DeviceEntity $device Device
     *
     * @return array
     */
    private function _transformDevice(DeviceEntity $device) {
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
