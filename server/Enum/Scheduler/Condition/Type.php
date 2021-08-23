<?php

namespace SmartHome\Enum\Scheduler\Condition;

use SplEnum;

/**
 * This file defines class for enum of condition types.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Type extends SplEnum {

    const LAST_RUN = 'last_run';
    const PING     = 'ping';
    const TIME     = 'time';
    const VALUE    = 'value';
    const OR       = 'or';

}
