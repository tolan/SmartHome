<?php

namespace SmartHome\Messaging\Workers\Device\Module\Abstracts;

use SmartHome\Enum\ModuleType;
use SmartHome\Entity\{
    Module,
    Control
};
use \SmartHome\Messaging\Workers\Device\Module\{
    Engine,
    Light
};

/**
 * This file defines abstract class for building module data consumed by client.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
abstract class Builder {

    /**
     * Module
     *
     * @var Module
     */
    private $_module;

    /**
     * Construct method for inject Module
     *
     * @param Module $module Module
     */
    public function __construct(Module $module) {
        $this->_module = $module;
    }

    /**
     * Prepare module data for client
     *
     * @return Control | null
     */
    abstract public function prepareForInit(): ?Control;

    /**
     * Builds control data for client
     *
     * @param array $control Control data
     *
     * @return array
     */
    abstract public function build(array $control): array;

    /**
     * Returns Module
     *
     * @return Module
     */
    protected function getModule(): Module {
        return $this->_module;
    }

    /**
     * Returns instance of module builder
     *
     * @param Module $module Module
     *
     * @return Builder
     */
    public static function getBuilder(Module $module): Builder {
        $type    = $module->getType();
        $builder = null;
        switch ($type) {
            case ModuleType::ENGINE:
                $builder = new Engine($module);
                break;
            case ModuleType::LIGHT:
                $builder = new Light($module);
                break;
        }

        return $builder;
    }

}
