import {Component, EventEmitter, Input, Output} from '@angular/core'

import {ControlType} from '../../../../../enums/controlType'

import {Control} from '../../../../../interfaces/control'

@Component({
    selector: 'device-row-module-control',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class DevicesRowModuleControlComponent {

    @Input('control') public control:Control
    @Input('isEditing') public isEditing: boolean = false
    @Output('onChange') onChange = new EventEmitter<Control>()

    public ControlType = ControlType

    changeControl(control: Control) {
        this.onChange.emit(control)
    }
}
