import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core'
import nextTick from 'next-tick'

import {TriggerEventType, TriggerEventTypeText} from '../../../../../../../enums/triggerType'

import {Data, Validation, Meta} from '../../../../../../../interfaces/task'


@Component({
    selector: 'trigger-event',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class TriggerEventComponent implements OnInit {

    @Input('value') public value: {type?: string, data?: any}
    @Output('onChange') onChange = new EventEmitter<Data>()
    @Output('onValidate') onValidate = new EventEmitter<Validation>()
    @Output('onMeta') onMeta = new EventEmitter<Meta>()

    public TriggerEventType = TriggerEventType
    public TriggerEventTypeText = Object.entries(TriggerEventTypeText)

    ngOnInit() {
        this.value = {
            type: null,
            ...this.value,
        }

        nextTick(() => this.onValidate.emit(this._validate(this.value)))
    }

    changeData(data: Data) {
        this.onChange.emit({
            type: this.value.type,
            data: data,
        })
    }

    changeValidation(validation: Validation) {
        this.onValidate.emit({...validation, isValid: validation.isValid && this._validate(this.value).isValid})
    }

    changeMeta(meta: Meta) {
        this.onMeta.emit(meta)
    }

    changeType(type: string) {
        this.onChange.emit({type})
    }

    private _validate(data: Data): Validation {
        return {isValid: !!data.type}
    }

}