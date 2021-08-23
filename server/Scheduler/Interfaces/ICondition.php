<?php

namespace SmartHome\Scheduler\Interfaces;

/**
 * This file defines interface for scheduler condition.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
interface ICondition {

    /**
     * Returns whether the condition is valid
     *
     * @return bool
     */
    public function isValid(): bool;

    /**
     * Returns last error message.
     *
     * @return string|null
     */
    public function getErrorMessage(): ?string;

}
