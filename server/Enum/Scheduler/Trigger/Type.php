<?php

namespace SmartHome\Enum\Scheduler\Trigger;

use SplEnum;

/**
 * This file defines class for enum of trigger types.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Type extends SplEnum {

    const EVENT = 'event';
    const TIME  = 'time';
    const MQTT  = 'mqtt';

}
