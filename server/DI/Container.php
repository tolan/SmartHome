<?php

namespace SmartHome\DI;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Container extends \DI\Container {

    public function isCreated (string $name): bool {
        return array_key_exists($name, $this->resolvedEntries);
    }

}
