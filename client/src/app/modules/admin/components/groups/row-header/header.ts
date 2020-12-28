import {Component, EventEmitter, Output} from '@angular/core';

@Component({
    selector: 'groups-row-header',
    templateUrl: './header.html',
    styleUrls: ['./header.less']
})
export class GroupsRowHeaderComponent {

    @Output('onAdd') onAdd = new EventEmitter();

    add() {
        this.onAdd.emit();
    }
}
