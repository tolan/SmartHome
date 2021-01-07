<?php

namespace SmartHome\Cache;

use Psr\SimpleCache\CacheInterface;
use SmartHome\Cache\Adapter\IAdapter;

/**
 * This file defines class for cache storage
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Storage implements CacheInterface {

    /**
     * Scope
     *
     * @var string
     */
    private $_scope;

    /**
     * Cache adapter
     *
     * @var IAdapter
     */
    private $_adapter;

    /**
     * Construct method for inject dependencies
     *
     * @param string   $scope   Scope
     * @param IAdapter $adapter Cache adapter
     */
    public function __construct(string $scope, IAdapter $adapter) {
        $this->_scope   = $scope;
        $this->_adapter = $adapter;
    }

    /**
     * Returns that the key exists
     *
     * @param string $key Key
     *
     * @return bool
     */
    public function has($key): bool {
        return $this->_adapter->has($this->_getKey($key));
    }

    /**
     * Gets value by key
     *
     * @param string $key     Key
     * @param mixed  $default Default value
     *
     * @return mixed
     */
    public function get($key, $default = null) {
        return $this->_adapter->get($this->_getKey($key), $default);
    }

    /**
     * Sets value by key
     *
     * @param string $key   Key
     * @param mixed  $value Value
     * @param int    $ttl   TTL
     *
     * @return bool
     */
    public function set($key, $value, $ttl = null): bool {
        return $this->_adapter->set($this->_getKey($key), $value, $ttl);
    }

    /**
     * Deletes value by key
     *
     * @param string $key Key
     *
     * @return bool
     */
    public function delete($key): bool {
        return $this->_adapter->delete($this->_getKey($key));
    }

    /**
     * Removes all values
     *
     * @return bool
     */
    public function clear(): bool {
        return $this->_adapter->clear();
    }

    /**
     * Returns multiples keys
     *
     * @param array $keys    Set of keys
     * @param mixed $default Default value
     *
     * @return iterable
     */
    public function getMultiple($keys, $default = null): iterable {
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

    /**
     * Sets multiple values
     *
     * @param array $values Array with [key => value]
     * @param int   $ttl    TTL
     *
     * @return bool
     */
    public function setMultiple($values, $ttl = null): bool {
        $map = [];
        foreach ($values as $key => $value) {
            $map[$this->_getKey($key)] = $value;
        }

        return $this->_adapter->setMultiple($map, $ttl);
    }

    /**
     * Deletes multiple keys
     *
     * @param array $keys Keys
     *
     * @return bool
     */
    public function deleteMultiple($keys): bool {
        $map = [];
        foreach ($keys as $key) {
            $map[] = $this->_getKey($key);
        }

        return $this->_adapter->deleteMultiple($keys);
    }

    /**
     * Gets key by scope
     *
     * @param string $key Key
     *
     * @return string
     */
    private function _getKey($key) {
        return $this->_scope.'.'.$key;
    }

}
