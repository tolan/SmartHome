<?php

namespace SmartHome\Enum\Scheduler\Condition\Time;

use SplEnum;

/**
 * This file defines class for enum of time condition types.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class When extends SplEnum {

    const EXACT  = 'exact';
    const BEFORE = 'before';
    const AFTER  = 'after';

}
