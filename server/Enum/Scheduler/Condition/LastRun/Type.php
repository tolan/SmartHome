<?php

namespace SmartHome\Enum\Scheduler\Condition\LastRun;

use SplEnum;

/**
 * This file defines class for enum of last run operator types.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Type extends SplEnum {

    const LOWER_THAN   = 'lt';
    const GREATER_THAN = 'gt';

}
