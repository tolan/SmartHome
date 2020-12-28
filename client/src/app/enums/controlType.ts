export enum ControlType {
    SWITCH = 'switch',
    PWM = 'pwm',
    FADE = 'fade',
    MQTT = 'mqtt',
}

export enum UserControlType {
    SWITCH = 'switch',
    PWM = 'pwm',
    FADE = 'fade',
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
}

