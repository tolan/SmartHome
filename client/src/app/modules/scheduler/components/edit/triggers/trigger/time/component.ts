import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core'
import nextTick from 'next-tick'

import {TriggerMetaType, TriggerTimeType, TriggerTimeTypeText} from '../../../../../../../enums/triggerType'

import {Data, Validation, Meta} from '../../../../../../../interfaces/task'

@Component({
    selector: 'trigger-time',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class TriggerTimeComponent implements OnInit {

    @Input('value') public value: {type?: string, data?: any}
    @Output('onChange') onChange = new EventEmitter<Data>()
    @Output('onValidate') onValidate = new EventEmitter<Validation>()
    @Output('onMeta') onMeta = new EventEmitter<Meta>()

    public TriggerTimeType = TriggerTimeType
    public TriggerTimeTypeText = Object.entries(TriggerTimeTypeText)

    ngOnInit() {
        this.value = {
            type: null,
            data: null,
            ...this.value,
        }

        nextTick(() => {
            this.onMeta.emit({valueType: TriggerMetaType.TIME})
            this.onValidate.emit(this._validate(this.value))
        })
    }

    changeType(type: string) {
        this.onChange.emit({type})
    }

    changeData(data: Data) {
        const value: Data = {
            ...this.value,
            data: data,
        }

        this.onChange.emit(value)
    }

    changeValidation(validation: Validation) {
        this.onValidate.emit({...validation, isValid: validation.isValid && this._validate(this.value).isValid})
    }

    private _validate(data: Data): Validation {
        return {isValid: !!data.type}
    }
}