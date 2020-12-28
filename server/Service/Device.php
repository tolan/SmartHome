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

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Device {

    /**
     *
     * @var Service
     */
    private $_service;

    /**
     *
     * @var Cache\Storage;
     */
    private $_cache;

    /**
     * @Inject({"cache"})
     */
    public function __construct ($cache, Service $service) {
        /* @var $cache Cache\Factory */
        $this->_service = $service;
        $this->_cache = $cache->getCache(Enum\Cache::SCOPE_DEVICE);
    }

    public function receive (AMessage $message) {
        if (!($message->getData() instanceof Timer)) {
            $this->_cache->delete('*');
        }
    }

    public function getDevices () {
        $query = EntityQuery::create(DeviceEntity::class, [[Module::class, Control::class], [Firmware::class], [Room::class]]);
        $devices = $this->_service->find($query);

        return array_map(function (DeviceEntity $device) {
            return $this->_transformDevice($device);
        }, $devices);
    }

    public function getModules (array $ids = []) {
        $modules = [];
        foreach ($this->_cache->getMultiple($ids) as $id => $entity) {
            if ($entity) {
                $modules[$id] = unserialize($entity);
            }
        }

        if (($rest = array_diff($ids, array_keys($modules)))) {
            $query = EntityQuery::create(Module::class, [[Control::class]], ['id' => $rest]);
            foreach ($this->_service->find($query) as $module) { /* @var $module Module */
                $modules[$module->getId()] = $module;
                $this->_cache->set($module->getId(), serialize($module));
            }
        }

        ksort($modules);

        return array_values($modules);
    }

    private function _transformDevice (DeviceEntity $device) {
        $modules = [];
        foreach ($device->getModules()->toArray() as $module) {
            $modules[] = $this->_transformModule($module);
        }

        return [
            'device' => $device,
            'firmware' => $device->getFirmware(),
            'room' => $device->getRoom(),
            'modules' => $modules,
        ];
    }

    private function _transformModule (Module $module) {
        $controls = array_map(function(Control $control) {
            return ['control' => $control];
        }, $module->getControls()->toArray());

        return [
            'module' => $module,
            'controls' => $controls,
        ];
    }

}
