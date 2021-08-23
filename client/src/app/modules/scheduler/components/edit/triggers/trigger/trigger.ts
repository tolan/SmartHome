import {Component, EventEmitter, Input, Output} from '@angular/core'

import {Trigger, Data, Validation, Meta, Condition, Output as Out} from '../../../../../../interfaces/task'
import {TriggerType} from '../../../../../../enums/triggerType'
import {TriggerOutputDefault} from '../../../../../../enums/triggerOutput'

@Component({
    selector: 'trigger',
    templateUrl: './trigger.html',
    styleUrls: ['./trigger.less']
})
export class TriggerComponent {

    @Input('trigger') public trigger: Trigger
    @Output('onRemove') onRemove = new EventEmitter<Trigger>()
    @Output('onChange') onChange = new EventEmitter<Trigger>()
    @Output('onValidate') onValidate = new EventEmitter<Validation>()

    public TriggerType = TriggerType

    public dataValidation: Validation = {isValid: true}
    public conditionsValidation: Validation = {isValid: true}
    public outputValidation: Validation = {isValid: true}

    removeTrigger() {
        this.onRemove.emit(this.trigger)
    }

    changeEventData(data: Data) {
        const trigger = {...this.trigger.trigger, data}
        this.onChange.emit({...this.trigger, trigger})
    }

    changeEventValidation(validation: Validation) {
        this.dataValidation = validation
        this.onValidate.emit(this._validate())
    }

    changeEventMeta(meta: Meta) {
        const trigger: Trigger = {
            ...this.trigger,
            meta,
        }

        if (!this.trigger.meta || this.trigger.meta.valueType !== meta.valueType) {
            const output: Out = {
                ...this.trigger.output,
                defaults: meta.valueType ? TriggerOutputDefault[meta.valueType] : [],
            }

            trigger.output = output
        }

        this.onChange.emit(trigger)
    }

    changeConditionsData(conditions: Condition[]) {
        this.onChange.emit({...this.trigger, conditions})
    }

    changeConditionsValidation(validation: Validation) {
        this.conditionsValidation = validation
        this.onValidate.emit(this._validate())
    }

    changeOutputData(output: Out) {
        this.onChange.emit({...this.trigger, output})
    }

    changeOutputValidation(validation: Validation) {
        this.outputValidation = validation
        this.onValidate.emit(this._validate())
    }

    private _validate(): Validation {
        return {isValid: this.dataValidation.isValid && this.conditionsValidation.isValid && this.outputValidation.isValid}
    }

}