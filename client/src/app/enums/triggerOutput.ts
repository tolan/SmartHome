import {TriggerMetaType} from './triggerType';
import {Data} from '../interfaces/task';

export enum TriggerOutputKeys {
    VALUE = 'value',
    TIME = 'time',
    TOPIC = 'topic',
}

export const TriggerOutputDefault: {[key: string]: Data[]} = {
    [`${TriggerMetaType.VALUE}`]: [{
        key: TriggerOutputKeys.VALUE,
        value: 'Hodnota z události',
    }],
    [`${TriggerMetaType.TIME}`]: [{
        key: TriggerOutputKeys.TIME,
        value: 'Čas události',
    }],
    [`${TriggerMetaType.SUN}`]: [{
        key: TriggerOutputKeys.TIME,
        value: 'Čas události',
    }],
    [`${TriggerMetaType.MQTT}`]: [{
        key: TriggerOutputKeys.TOPIC,
        value: 'Topic',
    }, {
        key: TriggerOutputKeys.VALUE,
        value: 'Hodnota z události',
    }],
}