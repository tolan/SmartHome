import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core'
import nextTick from 'next-tick'

import {Condition, Validation} from '../../../../../../../interfaces/task'

@Component({
    selector: 'pingCondition',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class PingConditionComponent implements OnInit {

    @Input('condition') public condition: Condition
    @Output('onChange') onChange = new EventEmitter<Condition>()
    @Output('onValidate') onValidate = new EventEmitter<Validation>()

    public isValid: boolean = true

    ngOnInit() {
        nextTick(() => this.onValidate.emit(this._validate(this.condition)))
    }

    changeValue(ipAddress: string) {
        const condition: Condition = {
            ...this.condition,
            value: {...this.condition.value, ipAddress}
        }

        this.onValidate.emit(this._validate(condition))
        this.onChange.emit(condition)
    }

    private _validate(condition: Condition): Validation {
        this.isValid = condition.value.ipAddress && condition.value.ipAddress.length > 0
        return {isValid: this.isValid}
    }
}