import {Injectable} from '@angular/core'
import {UserService} from '../services/user'
import {Socket, SocketEvent} from '../lib/socket'
import {SocketEventType} from '../enums/socket'
import {User} from '../interfaces/user'

import {Subject} from 'rxjs'
import {filter} from 'rxjs/operators'

@Injectable()
export class SocketEventBuilder {

    constructor(private socket: Socket, private userService: UserService) {}

    sendRequest(method: string, uri: string, body: any) {
        const storage = this.userService.getUser()
        const id = `request-${Date.now()}`
        storage.subscribe((user: User) => {
            storage.unsubscribe(id)
            const event = {
                type: SocketEventType.REQUEST,
                data: {
                    method,
                    uri,
                    body,
                    'X-User-Login-Token': user.user.token,
                    'X-date': Date.now(),
                }
            }

            this.socket.send(event)
        }, id)
    }

    subscribe(type: string) {
        const subject = new Subject()
        const pipe = subject.pipe(
            filter(Boolean),
            filter((msg: SocketEvent) => msg.type === type)
        )

        const next = (msg) => subject.next(msg)
        const container = this.socket.get().subscribe(next)

        const event = {
            type: SocketEventType.SUBSCRIBE,
            data: type,
        }
        this.socket.send(event)

        const unsubscribe = (subscription) => {
            const originUnsub = subscription.unsubscribe
            return () => {
                container.unsubscribe(next)
                const event = {
                    type: SocketEventType.UNSUBSCRIBE,
                    data: type,
                }
                this.socket.send(event)
                subscription.unsubscribe = originUnsub
                subscription.unsubscribe()
            }
        }

        const originSubs = pipe.subscribe
        const subscribe = (cb: (data: any) => void) => {
            pipe.subscribe = originSubs
            const subscription = pipe.subscribe(cb)

            return Object.assign(subscription, {unsubscribe: unsubscribe(subscription)})
        }

        return Object.assign(pipe, {subscribe})
    }
}