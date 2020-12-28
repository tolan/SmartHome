import {Component, EventEmitter, Output} from '@angular/core';

@Component({
    selector: 'firmwares-row-header',
    templateUrl: './header.html',
    styleUrls: ['./header.less']
})
export class FirmwaresRowHeaderComponent {

    @Output('onAdd') onAdd = new EventEmitter();

    add() {
        this.onAdd.emit();
    }
}
