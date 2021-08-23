<?php

namespace SmartHome\Scheduler\Conditions;

use SmartHome\Scheduler\{
    Context,
    Condition,
    Interfaces\ICondition
};
use SmartHome\Documents\Scheduler;

/**
 * This file defines class for OR condition.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class OrCondition implements ICondition {

    /**
     * Condition document
     *
     * @var Scheduler\Condition\OrCondition
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
     * @param Scheduler\Condition\OrCondition $condition Condition document
     * @param Context                         $context   Context
     */
    public function __construct(Scheduler\Condition\OrCondition $condition, Context $context) {
        $this->_condition = $condition;
        $this->_context   = $context;
    }

    /**
     * Returns whether the condition is valid
     *
     * @return bool
     */
    public function isValid(): bool {
        $isValid    = false;
        $messages   = [];
        foreach ($this->_condition->getValue() as $values) {
            $conditions = array_map(function($value) {
                $condition = Scheduler\Abstracts\ACondition::createCondition($value['type']);
                $condition->setValue($value['value']);
                return $condition;
            }, $values);

            $condition = new Condition($conditions, $this->_context);
            $isValid   = $condition->isValid();

            if (!$isValid) {
                $messages[] = $condition->getErrorMessage();
            } else {
                break;
            }
        }

        $this->_message = null;
        if (!$isValid) {
            $this->_message = join(', ', $messages);
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
