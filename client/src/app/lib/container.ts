import {Injectable} from '@angular/core'

interface CallbackInterface {
    cb: (data: any) => void
    id: string
}

export class Listeners {

    private data: any
    private callbacks: CallbackInterface[]

    constructor() {
        this.data = undefined
        this.callbacks = []
    }

    call(data: any) {
        this.data = data
        this.callbacks.forEach((callback: CallbackInterface) => callback.cb(data))
    }

    subscribe(cb: (data: any) => void, id?: string) {
        this.callbacks = [].concat(this.callbacks, {cb: cb, id: id})
        if (this.data !== undefined) {
            this.call(this.data)
        }

        return this
    }

    unsubscribe(cbOrId: string | ((data: any) => void)) {
        this.callbacks = this.callbacks.filter((callback: CallbackInterface) => {
            return !(callback.cb === cbOrId || callback.id === cbOrId)
        })

        return this
    }
}

export class Container {

    static prefix: string = 'Container_'

    private listeners: {
        [key: string]: Listeners,
    }

    constructor() {
        this.listeners = {}
    }

    get(key: string) {
        if (!this.listeners[key]) {
            this.listeners[key] = new Listeners()

            if (localStorage.getItem(this._getKey(key))) {
                const data = JSON.parse(localStorage.getItem(this._getKey(key)))
                this.listeners[key].call(data)
            }
        }

        return this.listeners[key]
    }

    has(key: string) {
        return !!this.listeners[key] || !!localStorage.getItem(this._getKey(key))
    }

    set(key: string, data: any, cached: boolean = false) {
        this.get(key).call(data)
        if (cached) {
            localStorage.setItem(this._getKey(key), JSON.stringify(data))
        } else if (!cached && localStorage.getItem(this._getKey(key))) {
            localStorage.removeItem(this._getKey(key))
        }
    }

    unset(key: string) {
        localStorage.removeItem(this._getKey(key))
        delete(this.listeners[key])
    }

    private _getKey(key: string) {
        return Container.prefix + key
    }
}

@Injectable({
    providedIn: 'root'
})
export class ContainerFactory {

    containers: {
        [key: string]: Container,
    }

    constructor() {
        this.containers = {}
    }

    getContainer(context: string): Container {
        if (!this.containers[context]) {
            this.containers[context] = new Container()
        }
        return this.containers[context]
    }
}