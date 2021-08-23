import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core'

import {ControlTypeName, ControlType} from '../../../../../../enums/controlType'

import {Control} from '../../../../../../interfaces/control'

@Component({
    template: '',
})
export class DevicesRowModuleControlParentComponent implements OnInit {

    @Input('control') public control: Control
    @Input('isEditing') public isEditing: boolean = false
    @Output('onChange') onChange = new EventEmitter<Control>()

    public ControlName = ControlTypeName
    public ControlType = ControlType

    public data: {
        active: boolean,
    } = {
        active: false,
    }

    ngOnInit() {
        this.data.active = this.control.control.controlData.active
    }

    changeActive(value: {checked: boolean}) {
        this.data.active = value.checked
        this.changeControl()
    }

    changeControl() {
        const control: Control = {
            control: {
                ...this.control.control,
                controlData: {
                    ...this.control.control.controlData,
                    active: this.data.active,
                }
            }
        }

        this.onChange.emit(control)
    }
}
