import {Component, EventEmitter, Input, Output, OnInit, OnChanges} from '@angular/core'
import nextTick from 'next-tick'

import {Condition, Validation, Output as Out} from '../../../../../../../interfaces/task'

@Component({
    selector: 'orCondition',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class OrConditionComponent implements OnInit, OnChanges {

    @Input('condition') public condition: Condition
    @Input('triggerType') public triggerType: string
    @Input('output') public output: Out
    @Output('onChange') onChange = new EventEmitter<Condition>()
    @Output('onValidate') onValidate = new EventEmitter<Validation>()

    private validation: boolean[] = []

    ngOnInit() {
        nextTick(() => this.onValidate.emit(this._validate(this.condition)))
    }

    ngOnChanges() {
        this.validation = this.condition.value.map(() => true)
    }

    addConditions() {
        const condition = {...this.condition}
        condition.value = condition.value.concat([[]])
        this.validation.push(true)
        this.onValidate.emit(this._validate(condition))
        this.onChange.emit(condition)
    }

    removeConditions(conditions: Condition[], key: number) {
        const value = {...this.condition}
        value.value = value.value.filter((item: Condition[]) => item !== conditions)
        this.validation.splice(key, 1)
        this.onValidate.emit(this._validate(value))
        this.onChange.emit(value)
    }

    changeConditions(changed: Condition[], conditions: Condition[]) {
        const value = {...this.condition}
        value.value = value.value.map((item: Condition[]) => item === conditions ? (changed.length ? Object.assign(item, changed) : changed) : item)
        this.onChange.emit(value)
    }

    changeValidations(validation: Validation, key: number) {
        this.validation[key] = validation.isValid
        this.onValidate.emit({
            ...validation,
            isValid: this.validation.every((item) => item) && this._validate(this.condition).isValid
        })
    }

    private _validate(condition: Condition): Validation {
        return {isValid: condition.value.every((item: Condition[]) => item.length > 0)}
    }
}