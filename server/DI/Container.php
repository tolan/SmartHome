<?php

namespace SmartHome\DI;

/**
 * This file defines class for dependency injection container
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Container extends \DI\Container {

    /**
     * Returns that the class has already created
     *
     * @param string $name Class name
     *
     * @return bool
     */
    public function isCreated(string $name): bool {
        return array_key_exists($name, $this->resolvedEntries);
    }

}
