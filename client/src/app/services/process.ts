import {Injectable} from '@angular/core';
import {HttpClient, HttpHeaders} from '@angular/common/http';
import {ContainerFactory, Container} from '../lib/container';

import {interval} from 'rxjs';
import {timeInterval, filter} from 'rxjs/operators';

import {Process} from '../interfaces/process';

@Injectable({
    providedIn: 'root'
})
export class ProcessService {

    private container: Container;
    private subject: any;
    private activeMonitor: boolean = false;

    constructor(private http: HttpClient, containerFactory: ContainerFactory) {
        this.container = containerFactory.getContainer('ProcessService');
        this._fetchProcesses();

        this.subject = interval(1000);
        this.subject.pipe(
            timeInterval(),
            filter(() => Boolean(this.activeMonitor))
        ).subscribe(() => {
            this._fetchProcesses(true);
        });
    }

    getProcesses() {
        return this.container.get('processes');
    }

    enableMonitor() {
        this.activeMonitor = true;
        return this;
    }

    disableMonitor() {
        this.activeMonitor = false;
        return this;
    }

    restart(process: Process) {
        this.http.post('/api/0/process/restart', {...process}).subscribe(() => {
            this._fetchProcesses();
        });
    }

    private _fetchProcesses(silent: boolean = false) {
        const headers = silent ? new HttpHeaders({'X-Silent': 'true'}) : {};

        this.http.get('/api/0/processes', {headers}).subscribe((processes: [Process]) => {
            this.container.set('processes', processes);
        });
    }
}
