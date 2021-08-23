import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core'
import nextTick from 'next-tick'

import {Data, Validation} from '../../../../../../../../interfaces/task'

import {DAY, DayText} from '../../../../../../../../enums/days'

const DefaultData = Object.values(DAY).reduce((acc: {[key: string]: any}, day: string) => {
    acc[day] = null
    return acc
}, {})

@Component({
    selector: 'trigger-time-weekly',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class TriggerTimeWeeklyComponent implements OnInit {

    @Input('value') public value: {
        [key: string]: {times: [{hours: number, minutes: number}]}[],
    }
    @Output('onChange') onChange = new EventEmitter<Data>()
    @Output('onValidate') onValidate = new EventEmitter<Validation>()

    public Days = Object.entries(DayText)

    public isValid: boolean = true

    ngOnInit() {
        this.value = {
            ...DefaultData,
            ...this.value,
        }

        nextTick(() => this._emitValue(this.value))
    }

    toggleDay(day: string) {
        const data: Data = {
            ...this.value
        }
        data[day] = data[day] ? null : {times: [{hours: 12, minutes: 0}]}

        this._emitValue(data)
    }

    changeDayValue(day: string, data: {times: {hours: number, minutes: number}[]}) {
        const value: Data = {
            ...this.value
        }
        value[day] = data

        this._emitValue(value)
    }

    changeValidation(validation: Validation) {
        this.onValidate.emit({...validation, isValid: validation.isValid && this._validate(this.value).isValid})
    }

    private _emitValue(data: Data) {
        this.onValidate.emit(this._validate(data))
        this.onChange.emit(data)
    }

    private _validate(data: Data): Validation {
        this.isValid = Object.values(data).some((day) => day)
        return {isValid: this.isValid}
    }

}