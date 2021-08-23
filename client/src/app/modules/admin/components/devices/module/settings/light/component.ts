import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core'
import {FormBuilder} from '@angular/forms'

import {Module} from '../../../../../../../interfaces/module'

@Component({
    selector: 'device-row-module-light',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class DevicesRowModuleLightComponent implements OnInit {

    @Input('module') public module: Module
    @Input('isEditing') public isEditing: boolean = false
    @Output('onChange') onChange = new EventEmitter<Module>()

    public moduleForm: any
    public settingsForm: any

    constructor(private formBuilder: FormBuilder) {}

    ngOnInit() {
        this.moduleForm = this.formBuilder.group({
            name: this.module.module.name,
        })

        this.settingsForm = this.formBuilder.group({
            pin: (this.module.module.settingsData || {}).pin,
            channel: (this.module.module.settingsData || {}).channel,
            resolution: (this.module.module.settingsData || {}).resolution,
        })
    }

    changeModule() {
        const module: Module = {
            module: {
                ...this.module.module,
                ...this.moduleForm.value,
                settingsData: {
                    ...this.settingsForm.value,
                },
            },
        }

        this.onChange.emit(module)
    }
}