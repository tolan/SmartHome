<?php

namespace SmartHome\Scheduler\Interfaces;

/**
 * This file defines interface for scheduler action.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
interface IAction {

    /**
     * Performs action
     *
     * @return bool
     */
    public function execute(): bool;

    /**
     * Returns whether the actions are executable
     *
     * @return bool
     */
    public function isExecutable(): bool;

}
