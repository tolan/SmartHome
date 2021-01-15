<?php

namespace SmartHome\Enum;

use SplEnum;

/**
 * This file defines class for enum of process task state.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class ProcessTaskState extends SplEnum {

    const INIT       = 'init';
    const START      = 'start';
    const ACTIVE     = 'active';
    const INACTIVE   = 'inactive';
    const KEEP_ALIVE = 'keepAlive';

}
