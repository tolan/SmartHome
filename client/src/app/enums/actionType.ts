export enum ActionType {
    DEVICE = 'device',
    HTTP = 'http',
    MQTT = 'mqtt',
}

export const ActionTypeText = {
    [`${ActionType.DEVICE}`]: 'Zařízení',
    [`${ActionType.HTTP}`]: 'HTTP',
    [`${ActionType.MQTT}`]: 'MQTT',
}

export enum ActionHTTPMethods {
    GET = 'GET',
    POST = 'POST',
    PUT = 'PUT',
    DELETE = 'DELETE',
}