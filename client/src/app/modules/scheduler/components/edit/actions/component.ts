import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core'

import {Action, Trigger, Validation} from '../../../../../interfaces/task'

import {ActionTypeText} from '../../../../../enums/actionType'
import {AddMenuType} from '../../../../../enums/addMenuType'

@Component({
    selector: 'actions',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class ActionsComponent implements OnInit {

    @Input('actions') public actions: Action[] = []
    @Input('triggers') public triggers: Trigger[] = []
    @Output('onChange') onChange = new EventEmitter<Action[]>()
    @Output('onValidate') onValidate = new EventEmitter<Validation>()

    public ActionTypes = Object.entries(ActionTypeText).map(([id, title]) => ({id, title}))
    public AddMenuType = AddMenuType

    public validation: boolean[] = []

    ngOnInit() {
        this.validation = this.actions.map(() => true)
    }

    addAction(type: string) {
        const actions: Action[] = this.actions.concat({
            type,
            data: {},
        })
        this.validation.push(true)
        this.onChange.emit(actions)
    }

    removeAction(action: Action, key: number) {
        const actions: Action[] = this.actions.filter((item: Action) => item !== action)

        this.validation.splice(key, 1)
        this.onValidate.emit(this._validate())
        this.onChange.emit(actions)
    }

    changeAction(action: Action, key: number) {
        const actions = this.actions.map((item: Action, index: number) => {
            return index === key ? Object.assign(item, action) : item
        })

        this.onChange.emit(actions)
    }

    upAction(key: number) {
        const actions = [...this.actions]
        const action: Action = actions.splice(key, 1)[0]
        actions.splice(key - 1, 0, action)

        this.validation.splice(key - 1, 0, this.validation.splice(key, 1)[0])

        this.onValidate.emit(this._validate())
        this.onChange.emit(actions)
    }

    downAction(key: number) {
        const actions = [...this.actions]
        const action: Action = actions.splice(key, 1)[0]
        actions.splice(key + 1, 0, action)

        this.validation.splice(key + 1, 0, this.validation.splice(key, 1)[0])

        this.onValidate.emit(this._validate())
        this.onChange.emit(actions)
    }

    changeActionValidation(validation: Validation, key: number) {
        this.validation[key] = validation.isValid
        this.onValidate.emit(this._validate())
    }

    private _validate(): Validation {
        return {isValid: this.validation.length > 0 && this.validation.every((isValid) => isValid)}
    }

}