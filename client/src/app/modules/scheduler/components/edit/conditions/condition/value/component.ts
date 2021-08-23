import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core';
import nextTick from 'next-tick'

import {Condition, Validation, Data, Output as Out} from '../../../../../../../interfaces/task';

import {TriggerValueConditionText} from '../../../../../../../enums/triggerCondition';

@Component({
    selector: 'valueCondition',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class ValueConditionComponent implements OnInit {

    @Input('condition') public condition: Condition;
    @Input('output') public output: Out;
    @Output('onChange') onChange = new EventEmitter<Condition>();
    @Output('onValidate') onValidate = new EventEmitter<Validation>();

    public TriggerValueConditionText = Object.entries(TriggerValueConditionText);

    public validation: {
        output: boolean,
        operator: boolean,
        value: boolean,
    } = {
            output: true,
            operator: true,
            value: true,
        }

    ngOnInit() {
        nextTick(() => this.onValidate.emit(this._validate(this.condition)))
    }

    changeOutput(output: string) {
        this._emitValue({output})
    }

    changeOutputValidation(validation: Validation) {
        this.validation.output = validation.isValid
        this.onValidate.emit({...validation, isValid: validation.isValid && this._validate(this.condition).isValid})
    }

    changeOperator(operator: string) {
        this._emitValue({operator})
    }

    changeValue(value: string) {
        this._emitValue({value})
    }

    private _emitValue(value: Data) {
        const condition = {
            ...this.condition,
            value: {
                ...this.condition.value,
                ...value,
            }
        }

        this.onValidate.emit(this._validate(condition))
        this.onChange.emit(condition);
    }

    private _validate(condition: Condition): Validation {
        this.validation = {
            ...this.validation,
            operator: !!condition.value.operator,
            value: !!(condition.value.value && condition.value.value.length > 0),
        }

        return {isValid: Object.values(this.validation).every((isValid) => isValid)}
    }
}