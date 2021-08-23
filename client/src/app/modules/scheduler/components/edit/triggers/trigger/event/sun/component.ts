import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core'
import nextTick from 'next-tick'

import {Data, Validation, Meta} from '../../../../../../../../interfaces/task'

import {
    TriggerMetaType,
    TriggerEventSunTypeText,
    TriggerEventSunDelay,
    TriggerEventSunDelayText
} from '../../../../../../../../enums/triggerType'

@Component({
    selector: 'trigger-event-sun',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class TriggerEventSunComponent implements OnInit {

    @Input('value') public value: {
        type?: string,
        delayType?: string,
        times?: {hours: number, minutes: number}[],
    }
    @Output('onChange') onChange = new EventEmitter<Data>()
    @Output('onValidate') onValidate = new EventEmitter<Validation>()
    @Output('onMeta') onMeta = new EventEmitter<Meta>()

    public TriggerEventSunTypeText = Object.entries(TriggerEventSunTypeText)
    public TriggerEventSunDelayText = Object.entries(TriggerEventSunDelayText)
    public DelaysWithTimePicker: string[] = [
        TriggerEventSunDelay.BEFORE,
        TriggerEventSunDelay.AFTER,
    ]

    public defaultTime: {hours: number, minutes: number} = {
        hours: 0,
        minutes: 0,
    }

    public validation: {
        type: boolean,
        delayType: boolean,
    } = {
        type: true,
        delayType: true,
    }

    ngOnInit() {
        this.value = {
            type: null,
            delayType: null,
            ...this.value,
        }

        nextTick(() => {
            this.onMeta.emit({valueType: TriggerMetaType.SUN})
            this.onValidate.emit(this._validate(this.value))
        })
    }

    changeType(type: string) {
        const data: Data = {
            type,
        }

        this._emitData(data)
    }

    changeDelayType(delayType: string) {
        const data: Data = {
            type: this.value.type,
            delayType,
            times: [{...this.defaultTime}]
        }

        this._emitData(data)
    }

    changeTimes(data: {times: {hours: number, minutes: number}[]}) {
        this._emitData({
            ...this.value,
            ...data,
        })
    }

    changeTimesValidation(validation: Validation) {
        this.onValidate.emit({...validation, isValid: validation.isValid && this._validate(this.value).isValid})
    }

    private _emitData(data: Data) {
        this.onValidate.emit(this._validate(data))
        this.onChange.emit(data)
    }

    private _validate(data: Data): Validation {
        const requiredFields = ['type', 'delayType'].every((field) => {
            this.validation[field] = !!data[field]
            return data[field]
        })
        const isValid = Object.values(data).every((value) => !!value)
        return {isValid: isValid && requiredFields}
    }
}