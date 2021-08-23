import {Component, EventEmitter, Input, Output, OnInit, OnDestroy, OnChanges} from '@angular/core'
import nextTick from 'next-tick'

import {ActionControlTypes, ControlType, ControlTypeName, UpDownType} from '../../../../../../../enums/controlType'

import {Data, Trigger, Validation, Output as Out} from '../../../../../../../interfaces/task'
import {Device} from '../../../../../../../interfaces/device'
import {Room} from '../../../../../../../interfaces/room'
import {Module} from '../../../../../../../interfaces/module'
import {Control} from '../../../../../../../interfaces/control'

import {DeviceService} from '../../../../../../../services/device'
import {RoomService} from '../../../../../../../services/room'

import {getAll} from '../../../../../../../utils/service'
import {mergeOutputs} from '../../../output/utils'

interface Action {
    type: string,
    name: string,
}

@Component({
    selector: 'deviceAction',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class DeviceActionComponent implements OnInit, OnDestroy, OnChanges {

    @Input('data') public data: Data
    @Input('triggers') public triggers: Trigger[] = []
    @Output('onChange') onChange = new EventEmitter<Data>()
    @Output('onValidate') onValidate = new EventEmitter<Validation>()

    private devices: Device[] = []

    public rooms: Room[] = []

    public selectableModules: Module[] = []
    public selectableActions: Action[] = []

    public ControlType = ControlType
    public UpDownType = UpDownType

    private subscribersKeys: string[]

    private lastPosition: number = 0

    public validation: {
        room: boolean,
        module: boolean,
        action: boolean,
        value: boolean,
    } = {
            room: true,
            module: true,
            action: true,
            value: true,
        }

    public selectableOutput: Out

    constructor(private roomService: RoomService, private deviceService: DeviceService) {}

    ngOnInit() {
        this.data = {
            room: null,
            module: null,
            action: null,
            value: null,
            ...this.data
        }

        this.subscribersKeys = getAll(this.roomService.getRooms(), this.deviceService.getDevices(), ([rooms]: [Room[]], [devices]: [Device[]]) => {
            this.rooms = rooms
            this.devices = devices
            if (this.data.room) {
                this._generateSelectableModules(this.data.room)

                if (this.data.module) {
                    this._generateSelectableActions(this.data.module)
                }
            }

            nextTick(() => this.onValidate.emit(this._validate(this.data)))
        })
    }

    ngOnDestroy() {
        [
            this.roomService.getRooms(),
            this.deviceService.getDevices()
        ].map((listener, key) => {
            listener.unsubscribe(this.subscribersKeys[key])
        })
    }

    ngOnChanges() {
        this.selectableOutput = mergeOutputs(this.triggers)
    }

    changeRoom(id: number) {
        this._generateSelectableModules(id)
        const data: Data = {
            room: id,
        }

        this.onValidate.emit(this._validate(data))
        this.onChange.emit(data)
    }

    changeDeviceModule(id: number) {
        this._generateSelectableActions(id)
        const data: Data = {
            room: this.data.room,
            module: id,
        }

        this.onValidate.emit(this._validate(data))
        this.onChange.emit(data)
    }

    changeDeviceAction(action: string) {
        this.lastPosition = null
        this.data.value = null
        if (action === ControlType.FADE) {
            this.data.delay = 1
        } else {
            delete (this.data.delay)
        }

        const data: Data = {
            ...this.data,
            action,
        }

        this.onValidate.emit(this._validate(data))
        this.onChange.emit(data)
    }

    changeSwitch(value: any) {
        console.error(value);
        const data = {
            ...this.data,
            value
        }

        this.onValidate.emit(this._validate(data))
        this.onChange.emit(data)
    }

    changeInput(event: {target: {value: string, selectionStart: number}}) {
        const value = event.target.value
        this.lastPosition = event.target.selectionStart
        if (value !== this.data.value) {
            const data = {...this.data, value}

            this.onValidate.emit(this._validate(data))
            this.onChange.emit(data)
        }
    }

    changeDelay(delay: number) {
        const data = {
            ...this.data,
            delay
        }

        this.onValidate.emit(this._validate(data))
        this.onChange.emit(data)
    }

    onOutputSelect(key: string) {
        let str: string = this.data.value || ''
        str = str.substr(0, this.lastPosition) + '${' + key + '}' + str.substr(this.lastPosition)
        const data = {...this.data, value: str}

        this.onValidate.emit(this._validate(data))
        this.onChange.emit(data)
    }

    _generateSelectableModules(roomId: number) {
        this.selectableModules = this.devices.reduce((modules: Module[], device: Device) => {
            if (device.room.id === roomId) {
                modules = modules.concat(device.modules)
            }

            return modules
        }, [])
    }

    _generateSelectableActions(moduleId: number) {
        const module: Module = this.devices.reduce((module: Module, device: Device) => {
            if (!module) {
                module = device.modules.find((item: Module) => item.module.id === moduleId)
            }

            return module
        }, null)

        this.selectableActions = ActionControlTypes.reduce((actions: Action[], action) => {
            const control = module.controls.find((control: Control) => control.control.type === action)
            if (control) {
                actions = actions.concat({
                    type: action,
                    name: ControlTypeName[action],
                })
            }

            return actions
        }, [])
    }

    private _validate(data: Data): Validation {
        this.validation = Object.keys(this.validation)
            .reduce((acc: {room: boolean, module: boolean, action: boolean, value: boolean}, key: string) => {
                acc[key] = (key === 'value') ? this._validateValue(data) : !!data[key]
                return acc
            }, this.validation)

        return {isValid: Object.values(this.validation).every((isValid) => isValid)}
    }

    private _validateValue(data: Data): boolean {
        if (data.action === ControlType.SWITCH) {
            return data.value !== null
        }

        if (!data.value) {
            return false
        }

        return true
    }
}