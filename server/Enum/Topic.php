<?php

namespace SmartHome\Enum;

use SplEnum;

/**
 * This file defines class for enum of known topics.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Topic extends SplEnum {

    const EVENT_MESSAGE          = 'smartHome/event/message';
    const PROCESS_STATE          = 'smartHome/process/state';
    const PROCESS_CONTROL        = 'smartHome/process/control';
    const PROCESS_INFO           = 'smartHome/process/info';
    const TIMER_START            = 'smartHome/timer/start';
    const TIMER_STOP             = 'smartHome/timer/stop';
    const DEVICE_KEEP_ALIVE      = 'smartHome/device/keepAlive';
    const DEVICE_KEEP_ALIVE_FAIL = 'smartHome/device/keepAlive/fail';
    const DEVICE_REGISTRATION    = 'smartHome/device/registration';
    const DEVICE_CONTROL         = 'smartHome/device/controlBasic';
    const DEVICE_CONTROL_REMOTE  = 'smartHome/device/controlRemote';
    const DEVICE_CONTROL_FADE    = 'smartHome/device/controlFade';
    const DEVICE_CONTROL_MQTT    = 'smartHome/device/controlMqtt';
    const DEVICE_RESTART         = 'smartHome/device/restart';
    const FIRMWARE_UPGRADE       = 'smartHome/firmware/upgrade';
    const SCHEDULER_TRIGGER_TIME = 'smartHome/scheduler/time';
    const SCHEDULER_TRIGGER_SUN  = 'smartHome/scheduler/sun';
    const SCHEDULER_TRIGGER_MQTT = 'smartHome/scheduler/mqtt';

}
