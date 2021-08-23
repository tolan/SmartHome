import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core'
import nextTick from 'next-tick'

import {Data, Validation, Meta} from '../../../../../../../interfaces/task'
import {TriggerMetaType} from '../../../../../../../enums/triggerType'

@Component({
    selector: 'trigger-mqtt',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class TriggerMQTTComponent implements OnInit {

    @Input('value') public value: {topic?: string, path?: string}
    @Output('onChange') onChange = new EventEmitter<Data>()
    @Output('onValidate') onValidate = new EventEmitter<Validation>()
    @Output('onMeta') onMeta = new EventEmitter<Meta>()

    public validation: {
        topic: boolean,
    } = {
            topic: true,
        }

    ngOnInit() {
        this.value = {
            topic: '',
            path: '',
            ...this.value
        }

        nextTick(() => {
            this.onMeta.emit({valueType: TriggerMetaType.MQTT})
            this.onValidate.emit(this._validate(this.value))
        })
    }

    changeValue(key: string, val: string) {
        const value: Data = {...this.value, [key]: val}
        this.onValidate.emit(this._validate(value))
        this.onChange.emit(value)
    }

    _validate(data: Data): Validation {
        this.validation.topic = data.topic.length > 0
        return {isValid: this.validation.topic}
    }
}