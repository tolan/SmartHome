<?php

namespace SmartHome\Enum\Scheduler\Trigger;

use SplEnum;

/**
 * This file defines class for enum of time trigger types.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Time extends SplEnum {

    const DAILY   = 'daily';
    const WEEKLY  = 'weekly';
    const MONTHLY = 'monthly';

}
