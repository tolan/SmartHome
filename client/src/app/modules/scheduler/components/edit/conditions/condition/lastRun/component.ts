import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core'
import nextTick from 'next-tick'

import {Condition, Validation} from '../../../../../../../interfaces/task'

import {TriggerLastRunCondtionText} from '../../../../../../../enums/triggerCondition'

@Component({
    selector: 'lastRunCondition',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class LastRunConditionComponent implements OnInit {

    @Input('condition') public condition: Condition
    @Output('onChange') onChange = new EventEmitter<Condition>()
    @Output('onValidate') onValidate = new EventEmitter<Validation>()

    public TriggerLastRunCondtionText = Object.entries(TriggerLastRunCondtionText)

    public isValid: boolean = true

    ngOnInit() {
        nextTick(() => this.onValidate.emit(this._validate(this.condition)))
    }

    changeType(type: string) {
        const condition = {
            ...this.condition,
            value: {
                ...this.condition.value,
                type,
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
        this.isValid = !!condition.value.type
        return {isValid: this.isValid}
    }
}