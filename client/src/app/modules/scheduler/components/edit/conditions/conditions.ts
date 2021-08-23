import {Component, EventEmitter, Input, Output, OnInit, OnChanges} from '@angular/core'
import nextTick from 'next-tick'
import clone from 'clone'

import {Condition, Output as Out, Validation} from '../../../../../interfaces/task'
import {TriggerCondition, TriggerConditionText, TriggerConditionDefault, TriggerConditionMetaTypeMap} from '../../../../../enums/triggerCondition'
import {AddMenuType} from '../../../../../enums/addMenuType'

@Component({
    selector: 'conditions',
    templateUrl: './conditions.html',
    styleUrls: ['./conditions.less']
})
export class ConditionsComponent implements OnInit, OnChanges {

    @Input('conditions') public conditions: Condition[] = []
    @Input('triggerType') public triggerType: string
    @Input('output') public output: Out
    @Input('allowEmpty') private allowEmpty: boolean = true
    @Input('addMenuYPosition') public addMenuYPosition: string = 'below'
    @Output('onChange') onChange = new EventEmitter<Condition[]>()
    @Output('onValidate') onValidate = new EventEmitter<Validation>()

    public TriggerCondition = TriggerCondition
    public TriggerConditionTypes: {id: string, title: string}[]
    public TriggerConditionMetaTypeMap = TriggerConditionMetaTypeMap
    public AddMenuType = AddMenuType

    public validation: boolean[] = []

    ngOnInit() {
        const map = this.triggerType ? TriggerConditionMetaTypeMap[this.triggerType] : Object.values(TriggerCondition)
        this.TriggerConditionTypes = Object.entries(TriggerConditionText).filter(([type]) => map.includes(type)).map(([id, title]) => ({id, title}))

        nextTick(() => {
            this.onValidate.emit(this._validate(this.conditions))
        })
    }

    ngOnChanges() {
        this.validation = this.conditions.map(() => true)
    }

    addCondition(type: string) {
        const conditions = this.conditions.concat(clone(TriggerConditionDefault[type]))
        this.validation.push(true)
        this.onChange.emit(conditions)
    }

    removeCondition(condition: Condition, key: number) {
        const conditions = this.conditions.filter((item: Condition) => item !== condition)
        this.validation.splice(key, 1)
        this.onValidate.emit(this._validate(conditions))
        this.onChange.emit(conditions)
    }

    changeCondition(changed: Condition, condition: Condition) {
        const conditions = [...this.conditions].map((item: Condition) => item === condition ? Object.assign(item, changed) : item)
        this.onChange.emit(conditions)
    }

    changeValidation(validation: Validation, key: number) {
        this.validation[key] = validation.isValid
        this.onValidate.emit({
            ...validation,
            isValid: this.validation.every((item) => item) && this._validate(this.conditions).isValid
        })
    }

    private _validate(conditions: Condition[]): Validation {
        return {isValid: this.allowEmpty || conditions.length > 0}
    }
}