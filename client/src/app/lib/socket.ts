import {Injectable} from '@angular/core'
import {ContainerFactory, Container} from '../lib/container'

import {SocketEventType} from '../enums/socket'

import {interval} from 'rxjs'

export interface SocketEvent {
    type: string
    data: any
}

const KEEP_ALIVE_INTERVAL = 10000

@Injectable({
    providedIn: 'root'
})
export class Socket {
    private socket: WebSocket
    private container: Container
    private subject: any
    private lastKeepAlive: number
    private isOpen: boolean = false
    private events: SocketEvent[] = []

    constructor(containerFactory: ContainerFactory) {
        this.container = containerFactory.getContainer('Socket')
        this.connect()
    }

    public connect(): this {
        if (!this.socket || this.socket.readyState >= WebSocket.CLOSING) {
            this.socket = this.createWebSocket()
            this.socket.addEventListener('message', (event) => {
                const eventData: SocketEvent = JSON.parse(event.data)
                if (eventData && eventData.type === SocketEventType.KEEP_ALIVE) {
                    this.lastKeepAlive = eventData.data
                } else {
                    this.container.set('message', eventData)
                }
            })

            this.socket.addEventListener('error', (event) => {
                this.container.set('error', event)
            })

            this.socket.addEventListener('close', (event) => {
                console.error('Socket is closed! Trying to reconnect...')
                this.subject.unsubscribe()
                this.connect()
            })

            this.socket.addEventListener('open', (event) => {
                this.isOpen = true
                this.events.forEach((msg) => this.send(msg))
                this.events = []
                this.send({type: SocketEventType.KEEP_ALIVE, data: Date.now()})
                this.subject = interval(KEEP_ALIVE_INTERVAL).subscribe(() => {
                    const date = Date.now()
                    if (date - this.lastKeepAlive > (KEEP_ALIVE_INTERVAL * 2.5)) {
                        console.error('Something wrong is with socket connection! Trying to reconnect...');
                        this.close()
                        this.connect()
                    } else {
                        this.send({type: SocketEventType.KEEP_ALIVE, data: date})
                    }
                })
            })
        }

        return this
    }

    get() {
        return this.container.get('message')
    }

    error() {
        return this.container.get('error')
    }

    send(msg: SocketEvent): this {
        if (!this.isConnected()) {
            this.events.push(msg)
        } else {
            this.socket.send(JSON.stringify(msg))
        }
        return this
    }

    close(): this {
        this.socket.close()
        this.subject.unsubscribe()
        return this
    }

    private isConnected() {
        return this.isOpen
    }

    private createWebSocket() {
        return new WebSocket(`ws://${window.location.hostname}:8886/app`)
    }
}
