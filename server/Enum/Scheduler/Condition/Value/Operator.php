<?php

namespace SmartHome\Enum\Scheduler\Condition\Value;

use SplEnum;

/**
 * This file defines class for enum of value operator types.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Operator extends SplEnum {

    const LOWER_THAN            = 'lt';
    const LOWER_THAN_OR_EQUAL   = 'lte';
    const EQUAL                 = 'eq';
    const NOT_EQUAL             = 'neq';
    const GREATER_THAN          = 'gt';
    const GREATER_THAN_OR_EQUAL = 'gte';

}
