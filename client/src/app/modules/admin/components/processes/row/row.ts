import {Component, EventEmitter, Input, Output} from '@angular/core';

import {Process} from '../../../../../interfaces/process';

@Component({
    selector: 'processes-row',
    templateUrl: './row.html',
    styleUrls: ['./row.less']
})
export class ProcessesRowComponent {

    @Input('process') public process: Process;
    @Output('onRestart') onRestart = new EventEmitter<Process>();

    restart() {
        this.onRestart.emit(this.process);
    }

}