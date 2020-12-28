import {Container} from './container';

export class Mediator {

    private container: Container;

    constructor() {
        this.container = new Container();
    }

    emit(keys: string | string[], data: any) {
        keys = [].concat(keys);
        keys.forEach((key) => this.container.set(key, data));
    }

    on(keys: string | string[], cb: (data: any) => void) {
        keys = [].concat(keys);
        keys.forEach((key) => this.container.get(key).subscribe(cb));
    }
}

export const buildKeys = function (group: string, actions?: string[]|string): string[] {
    if (!actions) {
        actions = [
            EventAction.create,
            EventAction.update,
            EventAction.remove
        ];
    } else {
        actions = [].concat(actions);
    }

    return actions.map((action) => group + ':' + action);
}

export enum EventGroup {
    DEVICE = 'device',
    FIRMWARE = 'firmware',
    GROUP = 'group',
    PERMISSION = 'permission',
    ROOM = 'room',
    USER = 'user',
    USERS = 'users',
}

export enum EventAction {
    find = 'find',
    get = 'get',
    create = 'create',
    update = 'update',
    remove = 'remove',
}