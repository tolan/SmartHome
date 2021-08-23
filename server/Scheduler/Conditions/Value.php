<?php

namespace SmartHome\Scheduler\Conditions;

use SmartHome\Scheduler\Interfaces\ICondition;
use SmartHome\Scheduler\Context;
use SmartHome\Documents\Scheduler;
use SmartHome\Enum\Scheduler\Condition\Value\Operator;
use SmartHome\Documents\Scheduler\Abstracts\AOutput;

/**
 * This file defines class for value condition.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Value implements ICondition {

    /**
     * Condition document
     *
     * @var Scheduler\Condition\Value
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
     * @param Scheduler\Condition\Value $condition Condition document
     * @param Context                   $context   Context
     */
    public function __construct(Scheduler\Condition\Value $condition, Context $context) {
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
        $outputs = $this->_context->getOutputs();

        $output = current(
            array_filter($outputs, function (AOutput $output) use ($value) {
                return $output->getKey() === $value['output'];
            })
        );

        $isValid        = false;
        $this->_message = null;
        if ($output) { /* @var $output AOutput */
            switch ($value['operator']) {
                case Operator::EQUAL:
                    $isValid = (string)$output->getValue() === $value['value'];
                    break;
                case Operator::GREATER_THAN:
                    $isValid = (string)$output->getValue() > $value['value'];
                    break;
                case Operator::GREATER_THAN_OR_EQUAL:
                    $isValid = (string)$output->getValue() >= $value['value'];
                    break;
                case Operator::LOWER_THAN:
                    $isValid = (string)$output->getValue() < $value['value'];
                    break;
                case Operator::LOWER_THAN_OR_EQUAL:
                    $isValid = (string)$output->getValue() <= $value['value'];
                    break;
                case Operator::NOT_EQUAL:
                    $isValid = (string)$output->getValue() !== $value['value'];
                    break;
            }

            if (!$isValid) {
                $this->_message = 'Hodnota "'.$output->getValue().'" neodpovídá požadované hodnotě "'.$value['value'].'" za podmínky "'.$value['operator'].'".';
            }
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
