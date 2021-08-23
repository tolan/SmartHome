import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core'
import nextTick from 'next-tick'

import {Condition, Validation} from '../../../../../../../interfaces/task'

import {TriggerTimeCondtionText} from '../../../../../../../enums/triggerCondition'

@Component({
    selector: 'timeCondition',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class TimeConditionComponent implements OnInit {

    @Input('condition') public condition: Condition
    @Output('onChange') onChange = new EventEmitter<Condition>()
    @Output('onValidate') onValidate = new EventEmitter<Validation>()

    public TriggerTimeCondtionText = Object.entries(TriggerTimeCondtionText)

    public isValid: boolean = true

    ngOnInit() {
        nextTick(() => this.onValidate.emit(this._validate(this.condition)))
    }

    changeWhen(when: string) {
        const condition = {
            ...this.condition,
            value: {
                ...this.condition.value,
                when,
            }
        }

        this.onValidate.emit(this._validate(condition))
        this.onChange.emit(condition)
    }

    changeTime(time: {hours: number, minutes: number}) {
        const value = {
            ...this.condition.value,
            time,
        }

        this.onChange.emit({...this.condition, value})
    }

    private _validate(condition: Condition): Validation {
        this.isValid = !!condition.value.when
        return {isValid: this.isValid}
    }
}