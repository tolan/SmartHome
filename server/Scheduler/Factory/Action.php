<?php

namespace SmartHome\Scheduler\Factory;

use SmartHome\Documents\Scheduler\Abstracts\AAction;
use SmartHome\Enum\Scheduler\Action\Type;
use SmartHome\Scheduler\Actions;
use SmartHome\Scheduler\Interfaces\IAction;
use SmartHome\Scheduler\Context;
use SmartHome\Scheduler\Exception;

/**
 * This file defines factory class for actions.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Action {

    /**
     * Creates instance of scheduler action related to action document
     *
     * @param AAction $action  Action document
     * @param Context $context Scheduler context
     *
     * @return IAction
     *
     * @throws Exception
     */
    public static function create(AAction $action, Context $context): IAction {
        $result = null;
        switch ($action->getType()) {
            case Type::DEVICE:
                $result = new Actions\Device($action, $context);
                break;
            case Type::HTTP:
                $result = new Actions\Http($action, $context);
                break;
            case Type::MQTT:
                $result = new Actions\Mqtt($action, $context);
                break;
            default:
                throw new Exception('Unsupported condition type: '.$action->getType());
        }

        return $result;
    }

}
