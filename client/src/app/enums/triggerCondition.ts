import {Condition} from '../interfaces/task';
import {TriggerMetaType} from './triggerType';

export enum TriggerCondition {
    LAST_RUN = 'last_run',
    PING = 'ping',
    TIME = 'time',
    VALUE = 'value',
    OR = 'or',
}

export const TriggerConditionText = {
    [`${TriggerCondition.LAST_RUN}`]: 'Poslední spuštení',
    [`${TriggerCondition.PING}`]: 'Ping',
    [`${TriggerCondition.TIME}`]: 'Čas',
    [`${TriggerCondition.VALUE}`]: 'Hodnota',
    [`${TriggerCondition.OR}`]: 'Nebo',
}

export enum TriggerTimeCondtion {
    EXACT = 'exact',
    BEFORE = 'before',
    AFTER = 'after',
}

export const TriggerTimeCondtionText = {
    [`${TriggerTimeCondtion.EXACT}`]: 'přesně v',
    [`${TriggerTimeCondtion.BEFORE}`]: 'před',
    [`${TriggerTimeCondtion.AFTER}`]: 'po',
}

export enum TriggerValueCondition {
    LOWER_THAN = 'lt',
    LOWER_THAN_OR_EQUAL = 'lte',
    EQUAL = 'eq',
    NOT_EQUAL = 'neq',
    GREATER_THAN = 'gt',
    GREATER_THAN_OR_EQUAL = 'gte',
}

export const TriggerValueConditionText = {
    [`${TriggerValueCondition.LOWER_THAN}`]: '<',
    [`${TriggerValueCondition.LOWER_THAN_OR_EQUAL}`]: '<=',
    [`${TriggerValueCondition.EQUAL}`]: '=',
    [`${TriggerValueCondition.NOT_EQUAL}`]: '!=',
    [`${TriggerValueCondition.GREATER_THAN}`]: '>',
    [`${TriggerValueCondition.GREATER_THAN_OR_EQUAL}`]: '>=',
}

export enum TriggerLastRunCondtion {
    LOWER_THAN = 'lt',
    GREATER_THAN = 'gt',
}

export const TriggerLastRunCondtionText = {
    [`${TriggerLastRunCondtion.LOWER_THAN}`]: 'méně než',
    [`${TriggerLastRunCondtion.GREATER_THAN}`]: 'více než',
}

export const TriggerConditionDefault: {[key: string]: Condition} = {
    [`${TriggerCondition.OR}`]: {
        type: TriggerCondition.OR,
        value: [[], []],
    },
    [`${TriggerCondition.PING}`]: {
        type: TriggerCondition.PING,
        value: {
            ipAddress: null,
        }
    },
    [`${TriggerCondition.TIME}`]: {
        type: TriggerCondition.TIME,
        value: {
            when: null,
            time: {hours: 0, minutes: 0},
        }
    },
    [`${TriggerCondition.VALUE}`]: {
        type: TriggerCondition.VALUE,
        value: {
            value: null,
        }
    },
    [`${TriggerCondition.LAST_RUN}`]: {
        type: TriggerCondition.LAST_RUN,
        value: {
            type: null,
            time: {hours: 0, minutes: 0},
        }
    },
}

export const TriggerConditionMetaTypeMap: {[key: string]: string[]} = {
    [`${TriggerMetaType.GENERAL}`]: [TriggerCondition.OR, TriggerCondition.LAST_RUN, TriggerCondition.PING, TriggerCondition.TIME, TriggerCondition.VALUE],
    [`${TriggerMetaType.VALUE}`]: [TriggerCondition.OR, TriggerCondition.LAST_RUN, TriggerCondition.PING, TriggerCondition.TIME, TriggerCondition.VALUE],
    [`${TriggerMetaType.TIME}`]: [TriggerCondition.OR, TriggerCondition.LAST_RUN, TriggerCondition.PING],
    [`${TriggerMetaType.SUN}`]: [TriggerCondition.OR, TriggerCondition.LAST_RUN, TriggerCondition.PING, TriggerCondition.TIME],
    [`${TriggerMetaType.MQTT}`]: [TriggerCondition.OR, TriggerCondition.LAST_RUN, TriggerCondition.PING, TriggerCondition.TIME, TriggerCondition.VALUE],
}