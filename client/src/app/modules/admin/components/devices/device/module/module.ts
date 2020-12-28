import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core';
import {FormBuilder} from '@angular/forms';

import {ControlType} from '../../../../../../enums/controlType';

import {Module} from '../../../../../../interfaces/module';
import {Control} from '../../../../../../interfaces/control';

@Component({
    selector: 'device-row-module',
    templateUrl: './module.html',
    styleUrls: ['./module.less']
})
export class DevicesRowModuleComponent implements OnInit {

    @Input('module') public module: Module;
    @Input('isEditing') public isEditing: boolean = false;
    @Output('onChange') onChange = new EventEmitter<{module: Module, changed: Module}>();

    public moduleForm: any;
    public settingsForm: any;

    public availableControlTypes: string[];
    private controls: any[] = [];

    constructor(private formBuilder: FormBuilder) {
        this.availableControlTypes = Object.values(ControlType);
    }

    ngOnInit() {
        this.controls = [].concat(this.module.controls);

        this.moduleForm = this.formBuilder.group({
            name: this.module.module.name,
        });

        this.settingsForm = this.formBuilder.group({
            pin: (this.module.module.settingsData || {}).pin,
            channel: (this.module.module.settingsData || {}).channel,
            resolution: (this.module.module.settingsData || {}).resolution,
        });
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
            controls: [].concat(this.controls),
        };

        this.onChange.emit({module: this.module, changed: module});
    }

    changeControl(control: {type: string, active: boolean, controlData: {}}) {
        this.controls = this.controls.filter((item: Control) => {
            return item.control.type !== control.type;
        }).concat({control});
        this.changeModule();
    }

    getControlData(type: string) {
        const control = {
            type: type,
            controlData: {
                active: false,
            },
        };

        return (this.controls || []).find((item: {control: {type: string}}) => {
            return item.control.type === type
        }) || {control};
    }
}
