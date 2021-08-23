<?php

namespace SmartHome\Scheduler\Conditions;

use SmartHome\Scheduler\Interfaces\ICondition;
use SmartHome\Scheduler\Context;
use SmartHome\Documents\Scheduler;
use SmartHome\Enum\Scheduler\Condition\LastRun\Type;

/**
 * This file defines class for last run condition.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class LastRun implements ICondition {

    const SEC_IN_MIN = 60;

    /**
     * Condition document
     *
     * @var Scheduler\Condition\LastRun
     */
    private $_condition;

    /**
     * Scheduler context
     *
     * @var Context
     */
    private $_context;

    /**
     * Last error message
     *
     * @var string|null
     */
    private $_message;

    /**
     * Construct method for inject dependencies
     *
     * @param Scheduler\Condition\LastRun $condition Condition document
     * @param Context                     $context   Context
     */
    public function __construct(Scheduler\Condition\LastRun $condition, Context $context) {
        $this->_condition = $condition;
        $this->_context   = $context;
    }

    /**
     * Returns whether the condition is valid
     *
     * @return bool
     */
    public function isValid(): bool {
        $value   = $this->_condition->getValue();
        $lastRun = $this->_context->getDocument()->getLastRun(); /* @var $lastRun \DateTime */
        if (!$lastRun) {
            return true;
        }

        $diff     = ($this->_context->getDate()->toDateTime()->getTimestamp() - $lastRun->getTimestamp());
        $treshold = (($value['time']['hours'] * self::SEC_IN_MIN + $value['time']['minutes']) * self::SEC_IN_MIN);

        $isValid = true;
        switch ($value['type']) {
            case Type::GREATER_THAN:
                $isValid        = $diff > $treshold;
                $this->_message = ($isValid) ? null : 'Poslední spuštění bylo méně než '.$value['time']['hours'].'h '.$value['time']['minutes'].'min.';
                break;
            case Type::LOWER_THAN:
                $isValid        = $diff < $treshold;
                $this->_message = ($isValid) ? null : 'Poslední spuštění bylo více než '.$value['time']['hours'].'h '.$value['time']['minutes'].'min.';
                break;
        }

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
