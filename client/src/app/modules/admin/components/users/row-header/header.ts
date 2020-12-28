import {Component, EventEmitter, Output} from '@angular/core';

@Component({
    selector: 'users-row-header',
    templateUrl: './header.html',
    styleUrls: ['./header.less']
})
export class UsersRowHeaderComponent {

    @Output('onAdd') onAdd = new EventEmitter();

    add() {
        this.onAdd.emit();
    }
}
