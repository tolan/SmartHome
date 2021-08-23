<?php

namespace SmartHome\Scheduler\Conditions;

use JJG;
use SmartHome\Scheduler\Interfaces\ICondition;
use SmartHome\Documents\Scheduler;

/**
 * This file defines class for ping condition.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Ping implements ICondition {

    /**
     * Condition document
     *
     * @var Scheduler\Condition\Ping
     */
    private $_condition;

    /**
     * Last error message
     *
     * @var string|null
     */
    private $_message;

    /**
     * Construct method for inject dependencies
     *
     * @param Scheduler\Condition\Ping $condition Condition document
     */
    public function __construct(Scheduler\Condition\Ping $condition) {
        $this->_condition = $condition;
    }

    /**
     * Returns whether the condition is valid
     *
     * @return bool
     */
    public function isValid(): bool {
        ['ipAddress' => $ipAddress] = $this->_condition->getValue();

        $ping = new JJG\Ping($ipAddress);
        $ping->setTimeout(3);

        $maxIteration = 2;
        do {
            $latency = $ping->ping();
        } while (!$latency && $maxIteration--);

        $isValid        = (bool)$latency;
        $this->_message = ($isValid) ? null : 'Zařízení "'.$ipAddress.'" není dostupné.';

        return $isValid;
    }

    /**
     * Returns last error message.
     *
     * @return string
     */
    public function getErrorMessage(): ?string {
        return $this->_message;
    }

}
