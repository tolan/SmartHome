import {Component, EventEmitter, Input, Output} from '@angular/core'

import {DevicesRowModuleControlParentComponent} from '../parent/component'

import {Control} from '../../../../../../interfaces/control'

@Component({
    selector: 'device-row-module-control-switch',
    templateUrl: '../parent/template.html',
    styleUrls: ['../parent/style.less']
})
export class DevicesRowModuleControlSwitchComponent extends DevicesRowModuleControlParentComponent {

    @Input('control') public control: Control
    @Input('isEditing') public isEditing: boolean = false
    @Output('onChange') onChange = new EventEmitter<Control>()

}
