<?php

namespace SmartHome\Enum\Scheduler\Action;

use SplEnum;

/**
 * This file defines class for enum of action types.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Type extends SplEnum {

    const DEVICE = 'device';
    const HTTP   = 'http';
    const MQTT   = 'mqtt';

}
