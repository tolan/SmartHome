import {Component, EventEmitter, Input, Output, OnChanges} from '@angular/core'

import {Trigger, Validation} from '../../../../../interfaces/task'
import {TriggerTypeText} from '../../../../../enums/triggerType'
import {AddMenuType} from '../../../../../enums/addMenuType'

@Component({
    selector: 'triggers',
    templateUrl: './triggers.html',
    styleUrls: ['./triggers.less']
})
export class TriggersComponent implements OnChanges {

    @Input('triggers') public triggers: Trigger[] = []
    @Output('onChange') onChange = new EventEmitter<Trigger[]>()
    @Output('onValidate') onValidate = new EventEmitter<Validation>()

    public triggerTypes = Object.entries(TriggerTypeText).map(([id, title]) => ({id, title}))
    public AddMenuType = AddMenuType

    public validation: boolean[] = []

    ngOnChanges() {
        this.validation = this.triggers.map(() => true)
    }

    addTrigger(type: string) {
        const triggers: Trigger[] = this.triggers.concat({
            trigger: {
                type: type,
                data: {},
            },
            conditions: [],
            output: {
                defaults: [],
                custom: [],
            },
            meta: {
                valueType: null,
            }
        })
        this.validation.push(true)
        this.onChange.emit(triggers)
    }

    removeTrigger(trigger: Trigger, key: number) {
        const triggers: Trigger[] = this.triggers.filter((item: Trigger) => item !== trigger)
        this.validation.splice(key, 1)
        this.onValidate.emit(this._validate())
        this.onChange.emit(triggers)
    }

    changeTrigger(data: Trigger, trigger: Trigger) {
        const triggers: Trigger[] = this.triggers.map((item: Trigger) => item === trigger ? Object.assign(item, data) : item)
        this.onChange.emit(triggers)
    }

    changeTriggerValidation(validation: Validation, key: number) {
        this.validation[key] = validation.isValid
        this.onValidate.emit(this._validate())
    }

    private _validate(): Validation {
        return {isValid: this.validation.length > 0 && this.validation.every((isValid) => isValid)}
    }
}