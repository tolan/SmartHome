import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core'
import nextTick from 'next-tick'

import {Data, Validation} from '../../../../../../../../interfaces/task'

const DAYS: number[] = Array.from({length: 31}, (_, i) => i + 1)

@Component({
    selector: 'trigger-time-monthly',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class TriggerTimeMonthlyComponent implements OnInit {

    @Input('value') public value: {
        days: {[key: number]: boolean},
        times: {hours: number, minutes: number}[],
    }
    @Output('onChange') onChange = new EventEmitter<Data>()
    @Output('onValidate') onValidate = new EventEmitter<Validation>()

    public DAYS = DAYS

    public validation: {
        days: boolean,
    } = {
        days: true,
    }

    ngOnInit() {
        this.value = {
            days: DAYS.reduce((acc: {[key: number]: boolean}, day: number) => {
                acc[day] = false
                return acc
            }, {}),
            times: [{hours: 12, minutes: 0}],
            ...this.value,
        }

        nextTick(() => this._emitValue(this.value))
    }

    changeDay(day: number) {
        const data: Data = {
            ...this.value
        }
        data.days[day] = !data.days[day]
        this._emitValue(data)
    }

    changeData(data: {times: {hours: number, minutes: number}[]}) {
        this._emitValue({
            ...this.value,
            times: data.times,
        })
    }

    changeValidation(validation: Validation) {
        this.onValidate.emit({...validation, isValid: validation.isValid && this._validate(this.value).isValid})
    }

    private _emitValue(data: Data) {
        this.onValidate.emit(this._validate(data))
        this.onChange.emit(data)
    }

    _validate(data: Data): Validation {
        this.validation.days = Object.values(data.days).some((day: boolean) => day)
        return {isValid: data.times.length > 0 && this.validation.days}
    }
}