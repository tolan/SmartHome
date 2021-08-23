import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core'

import {ControlsByModule} from '../../../../../enums/controlType'
import {ModuleType} from '../../../../../enums/moduleType'

import {Module} from '../../../../../interfaces/module'
import {Control} from '../../../../../interfaces/control'

@Component({
    selector: 'device-row-module',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class DevicesRowModuleComponent implements OnInit {

    @Input('module') public module: Module
    @Input('isEditing') public isEditing: boolean = false
    @Output('onChange') onChange = new EventEmitter<{module: Module, changed: Module}>()

    public ModuleType = ModuleType
    public availableControlTypes: string[]
    private controls: any[] = []

    ngOnInit() {
        this.availableControlTypes = ControlsByModule[this.module.module.type]
        this.controls = [].concat(this.module.controls)
    }

    changeModule(data: Module) {
        const module: Module = {
            module: {
                ...this.module.module,
                ...data.module,
            },
            controls: [].concat(this.controls),
        }
        this.onChange.emit({module: this.module, changed: module})
    }

    changeControl(control: Control) {
        this.controls = this.controls.filter((item: Control) => {
            return item.control.type !== control.control.type
        }).concat(control)
        this.changeModule(this.module)
    }

    getControlData(type: string) {
        const control = {
            type: type,
            controlData: {
                active: false,
            },
        }

        return (this.controls || []).find((item: {control: {type: string}}) => {
            return item.control.type === type
        }) || {control}
    }
}
