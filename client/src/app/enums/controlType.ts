import {ModuleType} from './moduleType';

export enum ControlType {
    SWITCH = 'switch',
    PWM = 'pwm',
    FADE = 'fade',
    MQTT = 'mqtt',
    UP_DOWN = 'up_down',
}

export enum UserControlType {
    SWITCH = 'switch',
    PWM = 'pwm',
    FADE = 'fade',
    UP_DOWN = 'up_down',
}

export enum UpDownType {
    UP = 'up',
    DOWN = 'down',
    STOP = 'stop',
}

export const MqttControlTypes = [
    ControlType.SWITCH,
    ControlType.PWM,
    ControlType.FADE,
];

export const ControlTypeName = {
    [`${ControlType.SWITCH}`]: 'On/Off',
    [`${ControlType.PWM}`]: 'Stmívání',
    [`${ControlType.FADE}`]: 'Slábnutí',
    [`${ControlType.MQTT}`]: 'MQTT',
    [`${ControlType.UP_DOWN}`]: 'Nahoru/Dolu',
}

export const TriggerControlTypes = [
    ControlType.SWITCH,
    ControlType.PWM,
    ControlType.UP_DOWN,
];

export const ActionControlTypes = [
    ControlType.SWITCH,
    ControlType.PWM,
    ControlType.FADE,
    ControlType.UP_DOWN,
]

export const ControlsByModule = {
    [`${ModuleType.LIGHT}`]: [
        ControlType.SWITCH,
        ControlType.PWM,
        ControlType.FADE,
        ControlType.MQTT,
    ],
    [`${ModuleType.ENGINE}`]: [
        ControlType.UP_DOWN
    ],
}