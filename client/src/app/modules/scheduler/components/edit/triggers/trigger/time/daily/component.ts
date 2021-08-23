import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core'
import nextTick from 'next-tick'

import {Data, Validation} from '../../../../../../../../interfaces/task'

interface Time {
    hours: number,
    minutes: number,
}

@Component({
    selector: 'trigger-time-daily',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class TriggerTimeDailyComponent implements OnInit {

    @Input('value') public value: {times: Time[]}
    @Input('default') private defaultValue: Time = {hours: 12, minutes: 0}
    @Output('onChange') onChange = new EventEmitter<Data>()
    @Output('onValidate') onValidate = new EventEmitter<Validation>()

    ngOnInit() {
        this.value = {
            times: [{...this.defaultValue}],
            ...this.value,
        }

        nextTick(() => this._emitValue(this.value))
    }

    addTime() {
        const times = this.value.times.concat({...this.defaultValue})
        this._emitValue({times})
    }

    removeTime(time: Time) {
        const times = this.value.times.filter((item) => item !== time)
        this._emitValue({times})
    }

    changeTime(changed: Time, time: Time) {
        const times = this.value.times.map((item) => item === time ? {...item, ...changed} : item)
        this._emitValue({times})
    }

    _emitValue(data: Data) {
        this.onValidate.emit(this._validate(data))
        this.onChange.emit(data)
    }

    _validate(data: Data): Validation {
        return {isValid: data.times.length > 0}
    }
}