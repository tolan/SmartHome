<?php

namespace SmartHome\Scheduler\Abstracts;

use SmartHome\Scheduler\Interfaces\IAction;
use SmartHome\Documents\Scheduler\Abstracts;
use SmartHome\Scheduler\Context;

/**
 * This file defines abstract class for scheduler action.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
abstract class AAction implements IAction {

    /**
     * Action document
     *
     * @var Abstracts\AAction
     */
    private $_action;

    /**
     * Scheduler context
     *
     * @var Context
     */
    private $_context;

    /**
     * Construct method for inject dependencies.
     *
     * @param Abstracts\AAction $action  Action document
     * @param Context           $context Context
     */
    public function __construct(Abstracts\AAction $action, Context $context) {
        $this->_action  = $action;
        $this->_context = $context;
    }

    /**
     * Gets Action document
     *
     * @return Abstracts\AAction
     */
    protected function getAction(): Abstracts\AAction {
        return $this->_action;
    }

    /**
     * Gets context
     *
     * @return Context
     */
    protected function getContext(): Context {
        return $this->_context;
    }

    /**
     * Translate value by outputs
     *
     * @param string $value Value
     *
     * @return string
     */
    protected function translateValue($value) {
        $map = array_reduce($this->_context->getOutputs(), function(array $acc, Abstracts\AOutput $output) {
            $acc['${'.$output->getKey().'}'] = $output->getValue();
            return $acc;
        }, []);

        $maxIteration = 10;
        do {
            $translated = $value;
            $translated = strtr($translated, $map);
        } while ($maxIteration-- && $translated !== $value);

        return $translated;
    }

}
