<?php

namespace SmartHome\Enum;

use SplEnum;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Topic extends SplEnum {

    const PROCESS_STATE = 'smartHome/process/state';
    const TIMER_START = 'smartHome/timer/start';
    const TIMER_STOP = 'smartHome/timer/stop';
    const DEVICE_KEEP_ALIVE = 'smartHome/device/keepAlive';
    const DEVICE_KEEP_ALIVE_FAIL = 'smartHome/device/keepAlive/fail';
    const DEVICE_REGISTRATION = 'smartHome/device/registration';
    const DEVICE_CONTROL = 'smartHome/device/controlBasic';
    const DEVICE_CONTROL_REMOTE = 'smartHome/device/controlRemote';
    const DEVICE_CONTROL_FADE = 'smartHome/device/controlFade';
    const DEVICE_CONTROL_MQTT = 'smartHome/device/controlMqtt';
    const DEVICE_RESTART = 'smartHome/device/restart';
    const FIRMWARE_UPGRADE = 'smartHome/firmware/upgrade';

}
