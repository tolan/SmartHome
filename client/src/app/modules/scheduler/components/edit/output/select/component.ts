import {Component, EventEmitter, Input, Output, OnChanges, ApplicationRef} from '@angular/core'
import nextTick from 'next-tick'

import {Validation, Output as Out} from '../../../../../../interfaces/task'

@Component({
    selector: 'output-select',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class OutputSelectComponent implements OnChanges {

    @Input('output') public output: Out
    @Input('selected') public selected: string
    @Input('required') public required: boolean = true
    @Output('onSelect') onSelect = new EventEmitter<string>()
    @Output('onValidate') onValidate = new EventEmitter<Validation>();

    public selectable: {key: string, value: string}[] = []

    public isValid: boolean = true

    constructor(private appRef: ApplicationRef) {}

    ngOnChanges() {
        this.selectable = [].concat(this.output.defaults, this.output.custom)

        nextTick(() => {
            if (!this.selected && this.selectable.length === 1) {
                this.selected = this.selectable[0].key
                this.onSelect.emit(this.selected)
            }
            const validation = this._validate(this.selected)
            this.onValidate.emit(validation)
            if (!validation.isValid) {
                nextTick(() => this.onSelect.emit(undefined))
            }
        })
    }

    onClick(key: string) {
        this.onValidate.emit(this._validate(key))
        this.onSelect.emit(key)
    }

    private _validate(key: string): Validation {
        const isValid = this.selectable.some((item: {key: string}) => item.key === key)

        if (isValid !== this.isValid) {
            this.isValid = isValid
            this.appRef.tick()
        }

        return {isValid}
    }
}