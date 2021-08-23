import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core'

import {ControlTypeName, ControlType, MqttControlTypes} from '../../../../../../enums/controlType'
import {Control} from '../../../../../../interfaces/control'

interface Mqtt {
    topic: string
    type: string
    value: string
}

@Component({
    selector: 'device-row-module-control-mqtt',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class DevicesRowModuleControlMqttComponent implements OnInit {

    @Input('control') public control: Control
    @Input('isEditing') public isEditing: boolean = false
    @Output('onChange') onChange = new EventEmitter<Control>()

    public ControlName = ControlTypeName
    public ControlType = ControlType
    public MqttControlTypes = MqttControlTypes

    public data: {
        active: boolean,
        mqtt: Mqtt[],
    } = {
        active: false,
        mqtt: [],
    }

    ngOnInit() {
        this.data.active = this.control.control.controlData.active
        this.data.mqtt = this.control.control.controlData.mqtt || []
    }

    changeActive(value: {checked: boolean}) {
        this.data.active = value.checked
        this.changeControl()
    }

    addMqtt() {
        this.data.mqtt.push({
            topic: '',
            type: ControlType.SWITCH,
            value: 'value',
        })
        this.changeControl()
    }

    removeMqtt(mqtt: Mqtt) {
        this.data.mqtt = this.data.mqtt.filter((item) => item !== mqtt)
        this.changeControl()
    }

    changeMqtt(mqtt: {[key: string]: string}, field: string, value: string) {
        mqtt[field] = value
        this.changeControl()
    }

    changeControl() {
        const control: Control = {
            control: {
                ...this.control.control,
                controlData: {
                    ...this.control.control.controlData,
                    active: this.data.active,
                    mqtt: this.data.mqtt,
                }
            }
        }

        this.onChange.emit(control)
    }
}
