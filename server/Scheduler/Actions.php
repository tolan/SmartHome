<?php

namespace SmartHome\Scheduler;

use SmartHome\Scheduler\Interfaces\IAction;
use SmartHome\Documents\Scheduler\Abstracts\AAction;

/**
 * This file defines class for performing actions.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Actions implements IAction {

    /**
     * List of actions
     *
     * @var AAction[]
     */
    private $_actions;

    /**
     * Scheduler context
     *
     * @var Context
     */
    private $_context;

    /**
     * Construct method for inject dependencies
     *
     * @param AAction[] $actions List of actions
     * @param Context   $context Context
     */
    public function __construct(array $actions, Context $context) {
        $this->_actions = $actions;
        $this->_context = $context;
    }

    /**
     * Performs all actions
     *
     * @return bool
     */
    public function execute(): bool {
        foreach ($this->_actions as $action) {
            Factory\Action::create($action, $this->_context)->execute();
        }

        return true;
    }

    /**
     * Returns whether the actions are executable
     *
     * @return bool
     */
    public function isExecutable(): bool {
        $isExecutable = true;
        foreach ($this->_actions as $action) {
            $isExecutable = Factory\Action::create($action, $this->_context)->isExecutable();
            if (!$isExecutable) {
                break;
            }
        }

        return $isExecutable;
    }

}
