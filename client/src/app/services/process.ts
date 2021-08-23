import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {ContainerFactory, Container} from '../lib/container';
import {SocketEventBuilder} from '../utils/socket'

import {SocketEventType} from '../enums/socket'

import {Process} from '../interfaces/process';

@Injectable({
    providedIn: 'root'
})
export class ProcessService {

    private container: Container;
    private subject: any;

    constructor(
        private http: HttpClient,
        private socketEventBuilder: SocketEventBuilder,
        containerFactory: ContainerFactory
    ) {
        this.container = containerFactory.getContainer('ProcessService');
    }

    getProcesses() {
        return this.container.get('processes');
    }

    enableMonitor() {
        this.subject = this.socketEventBuilder.subscribe(SocketEventType.PROCESS_STATES)
            .subscribe((data) => {
                this.container.set('processes', data.data.map((process) => {
                    return {
                        process: {
                            ...process,
                            runningTime: (Date.now() / 1000) - (process.startTime * 1000)
                        },
                    }
                }))
            })
        return this;
    }

    disableMonitor() {
        if (this.subject) {
            this.subject.unsubscribe()
        }
        return this;
    }

    restart(process: Process) {
        this.http.post('/api/0/process/restart', {...process}).subscribe()
    }
}
