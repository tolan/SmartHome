import {Component, EventEmitter, Input, Output} from '@angular/core'

import {AddMenuType} from '../../../enums/addMenuType';

interface Option {
    id: any,
    title: string,
}

@Component({
    selector: 'add-menu',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class AddMenuComponent {

    @Input('options') public options: Option[] = []
    @Input('type') public type: string = AddMenuType.PLUS
    @Input('yPosition') public yPosition: string = 'below'
    @Input('xPosition') public xPosition: string = 'after'
    @Output('onSelect') public onSelect = new EventEmitter<Option>()

    select(option: Option) {
        this.onSelect.emit(option)
    }
}