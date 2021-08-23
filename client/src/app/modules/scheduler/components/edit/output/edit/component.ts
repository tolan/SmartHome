import {Component, EventEmitter, Input, Output, OnInit, OnChanges} from '@angular/core';
import nextTick from 'next-tick'

import {Validation, Output as Out, Data} from '../../../../../../interfaces/task';

@Component({
    selector: 'output-edit',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class OutputEditComponent implements OnInit, OnChanges {

    @Input('output') public output: Out;
    @Output('onChange') onChange = new EventEmitter<Out>();
    @Output('onValidate') onValidate = new EventEmitter<Validation>()

    public validation: {
        [key: number]: {
            key: {
                required: boolean,
                unique: boolean,
            },
            value: boolean,
        },
    } = {};

    ngOnInit() {
        this.output = {
            defaults: [],
            custom: [],
            ...this.output,
        }

        this._validate(this.output)
        nextTick(() => this.onValidate.emit(this._validate(this.output)))
    }

    ngOnChanges() {
        nextTick(() => this.onValidate.emit(this._validate(this.output)))
    }

    changeOutputKey(data: Data, key: string) {
        const output = {
            ...this.output,
            custom: this.output.custom.map((item) => item === data ? Object.assign(item, {key}) : item)
        }

        this.onValidate.emit(this._validate(output))
        this.onChange.emit(output);
    }

    changeOutputValue(data: Data, value: string) {
        const output = {
            ...this.output,
            custom: this.output.custom.map((item) => item === data ? Object.assign(item, {value}) : item)
        }
        this.onValidate.emit(this._validate(output))
        this.onChange.emit(output);
    }

    removeOutput(data: Data) {
        const output = {
            ...this.output,
            custom: this.output.custom.filter((item) => item !== data)
        }

        this.onValidate.emit(this._validate(output))
        this.onChange.emit(output);
    }

    addOutput() {
        const output = {
            ...this.output,
            custom: this.output.custom.concat({
                key: this._getUniqueKey(this.output),
                value: null,
            }),
        }

        this.onValidate.emit(this._validate(output))
        this.onChange.emit(output);
    }

    private _validate(output: Out): Validation {
        this.validation = {}
        const isValid = output.custom
            .reduce((isValid, item: {key: string, value: string}, index: number, array: {key: string}[]) => {
                const requiredKey = !!(item.key && item.key.length > 0)
                this.validation[index] = {
                    key: {
                        required: requiredKey,
                        unique: !requiredKey || ![...output.defaults, ...array].some((record) => record.key === item.key && item !== record),
                    },
                    value: !!(item.value && item.value.length > 0),
                }

                return isValid && this.validation[index].key && this.validation[index].value
            }, true)

        return {
            isValid,
        }
    }

    private _getUniqueKey(output: Out): string {
        const keys = output.custom.map(({key}: {key: string}) => key)

        let title: number = output.custom.length + 1
        let maxIterations = 20
        while (keys.includes(title.toString()) && maxIterations--) {
            title++
        }

        return title.toString()
    }

}