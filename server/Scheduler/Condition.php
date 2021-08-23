<?php

namespace SmartHome\Scheduler;

use SmartHome\Scheduler\Interfaces\ICondition;
use SmartHome\Documents\Scheduler\Abstracts\ACondition;

/**
 * This file defines class for validate conditions.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Condition implements ICondition {

    /**
     * List of conditions
     *
     * @var ACondition[]
     */
    private $_conditions;

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
     * @param ACondition[] $conditions List of conditions
     * @param Context      $context    Context
     */
    public function __construct(array $conditions, Context $context) {
        $this->_conditions = $conditions;
        $this->_context    = $context;
    }

    /**
     * Returns whether the condition is valid
     *
     * @return bool
     */
    public function isValid(): bool {
        $isValid = true;
        foreach ($this->_conditions as $condition) {
            $schedulerCondition = Factory\Condition::create($condition, $this->_context);
            $isValid            = $schedulerCondition->isValid();
            $this->_message     = ($isValid) ? null : 'Nebyla splněna podmínka: '.$schedulerCondition->getErrorMessage();
            if (!$isValid) {
                break;
            }
        }

        return $isValid;
    }

    /**
     * Returns last error message.
     *
     * @return string|null
     */
    public function getErrorMessage(): ?string {
        return $this->_message;
    }

}
