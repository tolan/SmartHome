<?php

namespace SmartHome\Enum;

use \SplEnum;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class ControlType extends SplEnum {

    const SWITCH = 'switch';
    const PWM = 'pwm';
    const FADE = 'fade';
    const MQTT = 'mqtt';

}
