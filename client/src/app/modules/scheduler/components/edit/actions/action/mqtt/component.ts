import {Component, EventEmitter, Input, Output, OnInit, OnChanges} from '@angular/core'
import nextTick from 'next-tick'

import {Data, Trigger, Validation, Output as Out} from '../../../../../../../interfaces/task'

import {mergeOutputs} from '../../../output/utils'

@Component({
    selector: 'mqttAction',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class MQTTActionComponent implements OnInit {

    @Input('data') public data: Data = {}
    @Input('triggers') public triggers: Trigger[] = []
    @Output('onChange') onChange = new EventEmitter<Data>()
    @Output('onValidate') onValidate = new EventEmitter<Validation>()

    private lastEdited: {
        position: number,
        target: string,
    } = {
            position: null,
            target: null,
        }

    public validation: {
        topic: boolean,
    } = {
            topic: true,
        }

    public selectableOutput: Out

    ngOnInit() {
        this.data = {
            topic: '',
            message: '',
            ...this.data
        }

        nextTick(() => this.onValidate.emit(this._validate(this.data)))

    }

    ngOnChanges() {
        this.selectableOutput = mergeOutputs(this.triggers)
    }

    changeTopic(event: {target: {value: string, selectionStart: number}}) {
        const topic = event.target.value
        const position = event.target.selectionStart
        this.lastEdited = {target: 'topic', position}
        if (topic !== this.data.topic) {
            const data = {...this.data, topic}

            this.onValidate.emit(this._validate(data))
            this.onChange.emit(data)
        }
    }

    changeMessage(event: {target: {value: string, selectionStart: number}}) {
        const message = event.target.value
        const position = event.target.selectionStart
        this.lastEdited = {target: 'message', position}
        if (message !== this.data.message) {
            const data = {...this.data, message}
            this.onChange.emit(data)
        }
    }

    onOutputSelect(key: string) {
        if (this.lastEdited.target) {
            let str: string = this.data[this.lastEdited.target]
            str = str.substr(0, this.lastEdited.position) + '${' + key + '}' + str.substr(this.lastEdited.position)
            const data = {...this.data}
            data[this.lastEdited.target] = str

            this.onValidate.emit(this._validate(data))
            this.onChange.emit(data)
        }
    }

    private _validate(data: Data): Validation {
        this.validation = {
            topic: !!data.topic,
        }

        return {isValid: Object.values(this.validation).every((isValid) => isValid)}
    }
}