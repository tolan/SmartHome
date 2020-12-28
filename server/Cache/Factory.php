<?php

namespace SmartHome\Cache;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Factory {

    /**
     *
     * @var Adapter\IAdapter
     */
    private $_adapter;

    /**
     *
     * @var Storage[]
     */
    private $_instances = [];

    public function __construct (Adapter\IAdapter $adapter) {
        $this->_adapter = $adapter;
    }

    public function getCache (string $scope) {
        if (!array_key_exists($scope, $this->_instances)) {
            $this->_instances[$scope] = new Storage($scope, $this->_adapter);
        }

        return $this->_instances[$scope];
    }

}
