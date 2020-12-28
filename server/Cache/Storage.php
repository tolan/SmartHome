<?php

namespace SmartHome\Cache;

use Psr\SimpleCache\CacheInterface;
use SmartHome\Cache\Adapter\IAdapter;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Storage implements CacheInterface {

    /**
     *
     * @var string
     */
    private $_scope;

    /**
     *
     * @var IAdapter;
     */
    private $_adapter;

    public function __construct (string $scope, IAdapter $adapter) {
        $this->_scope = $scope;
        $this->_adapter = $adapter;
    }

    public function has ($key): bool {
        return $this->_adapter->has($this->_getKey($key));
    }

    public function get ($key, $default = null) {
        return $this->_adapter->get($this->_getKey($key), $default);
    }

    public function set ($key, $value, $ttl = null): bool {
        return $this->_adapter->set($this->_getKey($key), $value, $ttl);
    }

    public function delete ($key): bool {
        return $this->_adapter->delete($this->_getKey($key));
    }

    public function clear (): bool {
        return $this->_adapter->clear();
    }

    public function getMultiple ($keys, $default = null): iterable {
        $map = [];
        foreach ($keys as $key) {
            $map[$this->_getKey($key)] = $key;
        }

        $values = $this->_adapter->getMultiple(array_keys($map), $default);
        $result = [];

        foreach ($values as $key => $value) {
            $result[$map[$key]] = $value;
        }

        return $result;
    }

    public function setMultiple ($values, $ttl = null): bool {
        $map = [];
        foreach ($values as $key => $value) {
            $map[$this->_getKey($key)] = $value;
        }

        return $this->_adapter->setMultiple($map, $ttl);
    }

    public function deleteMultiple ($keys): bool {
        $map = [];
        foreach ($keys as $key) {
            $map[] = $this->_getKey($key);
        }

        return $this->_adapter->deleteMultiple($keys);
    }

    private function _getKey ($key) {
        return $this->_scope.'.'.$key;
    }

}
