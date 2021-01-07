<?php

namespace SmartHome\Cache;

/**
 * This file defines class for cache factory.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Factory {

    /**
     * Cache adapter
     *
     * @var Adapter\IAdapter
     */
    private $_adapter;

    /**
     * Set of storages by scope
     *
     * @var Storage[]
     */
    private $_instances = [];

    /**
     * Construct method for inject dependencies
     *
     * @param Adapter\IAdapter $adapter Cache adapter
     */
    public function __construct(Adapter\IAdapter $adapter) {
        $this->_adapter = $adapter;
    }

    /**
     * Returns instance of cache storage
     *
     * @param string $scope Scope
     *
     * @return Storage
     */
    public function getCache(string $scope): Storage {
        if (!array_key_exists($scope, $this->_instances)) {
            $this->_instances[$scope] = new Storage($scope, $this->_adapter);
        }

        return $this->_instances[$scope];
    }

}
