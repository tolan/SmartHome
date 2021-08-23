export enum TriggerType {
    EVENT = 'event',
    TIME = 'time',
    MQTT = 'mqtt',
}

export enum TriggerMetaType {
    GENERAL = 'general',
    VALUE = 'value',
    TIME = 'time',
    SUN = 'sun',
    MQTT = 'mqtt',
}

export const TriggerTypeText = {
    [`${TriggerType.EVENT}`]: 'Událost',
    [`${TriggerType.TIME}`]: 'Čas',
    [`${TriggerType.MQTT}`]: 'MQTT',
}

export enum TriggerEventType {
    DEVICE = 'device',
    SUN = 'sun',
}

export const TriggerEventTypeText = {
    [`${TriggerEventType.DEVICE}`]: 'Změna stavu zařízení',
    [`${TriggerEventType.SUN}`]: 'Východ / Západ slunce',
}

export enum TriggerEventSunType {
    SUNSET = 'sunset',
    SUNRISE = 'sunrise',
}

export const TriggerEventSunTypeText = {
    [`${TriggerEventSunType.SUNSET}`]: 'Západ',
    [`${TriggerEventSunType.SUNRISE}`]: 'Východ',
}

export enum TriggerEventSunDelay {
    ZERO = 'zero',
    BEFORE = 'before',
    AFTER = 'after',
}

export const TriggerEventSunDelayText = {
    [`${TriggerEventSunDelay.ZERO}`]: 'v přesný čas',
    [`${TriggerEventSunDelay.BEFORE}`]: 's předstihem',
    [`${TriggerEventSunDelay.AFTER}`]: 'se zpožděním',
}

export enum TriggerTimeType {
    DAILY = 'daily',
    WEEKLY = 'weekly',
    MONTHLY = 'monthly',
}

export const TriggerTimeTypeText = {
    [`${TriggerTimeType.DAILY}`]: 'Denní',
    [`${TriggerTimeType.WEEKLY}`]: 'Týdenní',
    [`${TriggerTimeType.MONTHLY}`]: 'Měsíční',
}