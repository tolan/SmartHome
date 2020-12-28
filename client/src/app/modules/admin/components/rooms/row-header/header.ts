import {Component, EventEmitter, Output} from '@angular/core';

@Component({
    selector: 'rooms-row-header',
    templateUrl: './header.html',
    styleUrls: ['./header.less']
})
export class RoomsRowHeaderComponent {

    @Output('onAdd') onAdd = new EventEmitter();

    add() {
        this.onAdd.emit();
    }
}
