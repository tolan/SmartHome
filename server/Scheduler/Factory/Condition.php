<?php

namespace SmartHome\Scheduler\Factory;

use SmartHome\Documents\Scheduler\Abstracts\ACondition;
use SmartHome\Enum\Scheduler\Condition\Type;
use SmartHome\Scheduler\Conditions;
use SmartHome\Scheduler\Interfaces\ICondition;
use SmartHome\Scheduler\Context;
use SmartHome\Scheduler\Exception;

/**
 * This file defines factory class for conditions.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Condition {

    /**
     * Creates instance of scheduler condition related to condition document
     *
     * @param ACondition $condition Condition document
     * @param Context    $context   Scheduler context
     *
     * @return ICondition
     *
     * @throws Exception
     */
    public static function create(ACondition $condition, Context $context): ICondition {
        $result = null;
        switch ($condition->getType()) {
            case Type::LAST_RUN:
                $result = new Conditions\LastRun($condition, $context);
                break;
            case Type::OR:
                $result = new Conditions\OrCondition($condition, $context);
                break;
            case Type::PING:
                $result = new Conditions\Ping($condition);
                break;
            case Type::TIME:
                $result = new Conditions\Time($condition, $context);
                break;
            case Type::VALUE:
                $result = new Conditions\Value($condition, $context);
                break;
            default:
                throw new Exception('Unsupported condition type: '.$condition->getType());
        }

        return $result;
    }

}
