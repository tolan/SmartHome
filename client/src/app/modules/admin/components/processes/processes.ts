import {Component, OnInit} from '@angular/core';

import {ProcessService} from '../../../../services/process';

import {Process} from '../../../../interfaces/process';

@Component({
    selector: 'processes',
    templateUrl: './processes.html',
    styleUrls: ['./processes.less']
})
export class ProcessesComponent implements OnInit {

    processes: Process[] = [];

    constructor(private processService: ProcessService) {}

    ngOnInit() {
        this.processService.enableMonitor().getProcesses().subscribe((processes: [Process]) => {
            this.processes = processes;
        }, 'ProcessesComponent');
    }

    ngOnDestroy() {
        this.processService.disableMonitor();
    }

    restart(process: Process) {
        this.processService.restart(process);
    }

}