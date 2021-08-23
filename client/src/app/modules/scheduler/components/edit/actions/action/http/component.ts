import {Component, EventEmitter, Input, Output, OnInit, OnChanges} from '@angular/core'
import nextTick from 'next-tick'

import {Data, Trigger, Output as Out, Validation} from '../../../../../../../interfaces/task'

import {ActionHTTPMethods} from '../../../../../../../enums/actionType'

import {mergeOutputs} from '../../../output/utils'

const METHODS: string[] = Object.values(ActionHTTPMethods)

@Component({
    selector: 'httpAction',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class HTTPActionComponent implements OnInit, OnChanges {

    @Input('data') public data: Data = {}
    @Input('triggers') public triggers: Trigger[] = []
    @Output('onChange') onChange = new EventEmitter<Data>()
    @Output('onValidate') onValidate = new EventEmitter<Validation>()

    public ActionHTTPMethods = ActionHTTPMethods
    public methodsSorter = (a: {key: string}, b: {key: string}) => METHODS.indexOf(a.key) - METHODS.indexOf(b.key)

    private updateLastEdited: (key: string) => Data

    public validation: {
        uri: boolean,
    } = {
            uri: true,
        }

    public selectableOutput: Out

    ngOnInit() {
        this.data = {
            method: ActionHTTPMethods.GET,
            uri: '',
            params: [],
            body: '',
            ...this.data
        }

        nextTick(() => this.onValidate.emit(this._validate(this.data)))
    }

    ngOnChanges() {
        this.selectableOutput = mergeOutputs(this.triggers)
    }

    changeMethod(method: string) {
        const data = {
            ...this.data,
            method,
        }

        this.onChange.emit(data)
    }

    changeUri(event: {target: {value: string, selectionStart: number}}) {
        const uri = event.target.value
        const position = event.target.selectionStart

        this.updateLastEdited = (key: string): Data => {
            const uri = this.injectString(key, this.data.uri, position)
            return {...this.data, uri}
        }

        if (uri !== this.data.uri) {
            const data = {...this.data, uri}

            this.onValidate.emit(this._validate(data))
            this.onChange.emit(data)
        }
    }

    addParam() {
        const data = {
            ...this.data,
            params: this.data.params.concat({key: null, value: null}),
        }

        this.onChange.emit(data)
    }

    changeParamKey(param: {key: string, value: string}, event: {target: {value: string, selectionStart: number}}) {
        const key = event.target.value
        const position = event.target.selectionStart

        this.updateLastEdited = (output: string): Data => {
            const key = this.injectString(output, param.key, position)
            const params = [...this.data.params].map((item) => item === param ? Object.assign(item, {key}) : item)

            return {...this.data, params}
        }

        const params = [...this.data.params].map((item) => item === param ? Object.assign(item, {key}) : item)
        const data = {...this.data, params}

        this.onChange.emit(data)
    }

    changeParamValue(param: {key: string, value: string}, event: {target: {value: string, selectionStart: number}}) {
        const value = event.target.value
        const position = event.target.selectionStart

        this.updateLastEdited = (output: string): Data => {
            const value = this.injectString(output, param.value, position)
            const params = this.data.params.map((item: any) => item === param ? Object.assign(item, {value}) : item)

            return {...this.data, params}
        }

        const params = this.data.params.map((item: any) => item === param ? Object.assign(item, {value}) : item)
        const data = {...this.data, params}

        this.onChange.emit(data)
    }

    removeParam(param: {key: string, value: string}) {
        const params = this.data.params.filter((item: any) => item !== param)
        const data = {...this.data, params}

        this.onChange.emit(data)
    }

    changeBody(event: {target: {value: string, selectionStart: number}}) {
        const body = event.target.value
        const position = event.target.selectionStart

        this.updateLastEdited = (key: string): Data => {
            const body = this.injectString(key, this.data.body, position)
            return {...this.data, body}
        }

        if (body !== this.data.body) {
            const data = {...this.data, body: body}

            this.onChange.emit(data)
        }
    }

    onOutputSelect(key: string) {
        if (this.updateLastEdited) {
            const data = this.updateLastEdited(key)

            this.onValidate.emit(this._validate(data))
            this.onChange.emit(data)
        }
    }

    private injectString(source: string, target: string, position: number) {
        return target.substr(0, position) + '${' + source + '}' + target.substr(position)
    }

    private _validate(data: Data): Validation {
        this.validation = {
            uri: !!data.uri,
        }

        return {isValid: Object.values(this.validation).every((isValid) => isValid)}
    }

}