<?php

namespace SmartHome\Enum\Scheduler\Trigger;

use SplEnum;

/**
 * This file defines class for enum of sun event trigger delay types.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class SunDelay extends SplEnum {

    const ZERO   = 'zero';
    const BEFORE = 'before';
    const AFTER  = 'after';

}
