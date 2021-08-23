export enum SocketEventType {
    KEEP_ALIVE = 'keepAlive',
    MESSAGE = 'smartHome/event/message',
    PROCESS_STATES = 'smarthome/event/processStates',
    REQUEST = 'smartHome/event/request',
    SUBSCRIBE = 'smartHome/event/subscribe',
    UNSUBSCRIBE = 'smartHome/event/unsubscribe',
}
