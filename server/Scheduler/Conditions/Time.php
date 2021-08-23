<?php

namespace SmartHome\Scheduler\Conditions;

use DateTimeZone;
use SmartHome\Scheduler\Interfaces\ICondition;
use SmartHome\Scheduler\Context;
use SmartHome\Documents\Scheduler;
use SmartHome\Enum\Scheduler\Condition\Time\When;

/**
 * This file defines class for time condition.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Time implements ICondition {

    /**
     * Condition document
     *
     * @var Scheduler\Condition\Time
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
     * @param Scheduler\Condition\Time $condition Condition document
     * @param Context                  $context   Context
     */
    public function __construct(Scheduler\Condition\Time $condition, Context $context) {
        $this->_condition = $condition;
        $this->_context   = $context;
    }

    /**
     * Returns whether the condition is valid
     *
     * @return bool
     */
    public function isValid(): bool {
        $actualDateTime = $this->_context->getDate()->toDateTime()->setTimezone(new DateTimeZone(date_default_timezone_get()));
        ['when' => $when, 'time' => $valueTime] = $this->_condition->getValue();
        ['hours' => $valueHours, 'minutes' => $valueMinutes] = $valueTime;

        $actualHours   = (int)$actualDateTime->format('H');
        $actualMinutes = (int)$actualDateTime->format('i');

        $isValid        = false;
        $this->_message = null;
        switch ($when) {
            case When::EXACT:
                $isValid = $actualHours === $valueHours && $actualMinutes === $valueMinutes;
                if (!$isValid) {
                    $this->_message = 'Přesný čas neodpovídá času spuštění '.$actualHours.'h '.$actualMinutes.'min =='.$valueHours.'h '.$valueMinutes.'min.';
                }
                break;
            case When::AFTER:
                $isValid = $actualHours > $valueHours || ($actualHours === $valueHours && $actualMinutes > $valueMinutes);
                if (!$isValid) {
                    $this->_message = 'Přesný čas neodpovídá času spuštění '.$actualHours.'h '.$actualMinutes.'min >'.$valueHours.'h '.$valueMinutes.'min.';
                }
                break;
            case When::BEFORE:
                $isValid = $actualHours < $valueHours || ($actualHours === $valueHours && $actualMinutes < $valueMinutes);
                if (!$isValid) {
                    $this->_message = 'Přesný čas neodpovídá času spuštění '.$actualHours.'h '.$actualMinutes.'min <'.$valueHours.'h '.$valueMinutes.'min.';
                }
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
