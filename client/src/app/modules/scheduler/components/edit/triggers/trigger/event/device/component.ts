import {Component, EventEmitter, Input, Output, OnInit, OnDestroy} from '@angular/core'
import nextTick from 'next-tick'

import {TriggerControlTypes, ControlTypeName} from '../../../../../../../../enums/controlType'
import {TriggerMetaType} from '../../../../../../../../enums/triggerType'

import {Data, Validation, Meta} from '../../../../../../../../interfaces/task'
import {Device} from '../../../../../../../../interfaces/device'
import {Room} from '../../../../../../../../interfaces/room'
import {Module} from '../../../../../../../../interfaces/module'
import {Control} from '../../../../../../../../interfaces/control'

import {DeviceService} from '../../../../../../../../services/device'
import {RoomService} from '../../../../../../../../services/room'

import {getAll} from '../../../../../../../../utils/service'

interface Action {
    type: string,
    name: string,
}

@Component({
    selector: 'trigger-event-device',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class TriggerEventDeviceComponent implements OnInit, OnDestroy {

    @Input('value') public value: {
        room?: number,
        module?: number,
        action?: string,
    }
    @Output('onChange') onChange = new EventEmitter<Data>()
    @Output('onValidate') onValidate = new EventEmitter<Validation>()
    @Output('onMeta') onMeta = new EventEmitter<Meta>()

    private devices: Device[] = []

    public rooms: Room[] = []

    public validation: {
        room: boolean,
        module: boolean,
        action: boolean,
    } = {
        room: true,
        module: true,
        action: true,
    }

    public selectableModules: Module[] = []
    public selectableActions: Action[] = []

    private subscribersKeys: string[] = []

    constructor(private roomService: RoomService, private deviceService: DeviceService) {}

    ngOnInit() {
        this.value = {
            room: null,
            module: null,
            action: null,
            ...this.value,
        }

        nextTick(() =>  {
            this.onMeta.emit({valueType: TriggerMetaType.VALUE})
            this.onValidate.emit(this._validate(this.value))
        })

        this.subscribersKeys = getAll(this.roomService.getRooms(), this.deviceService.getDevices(), ([rooms]: [Room[]], [devices]: [Device[]]) => {
            this.rooms = rooms
            this.devices = devices
            if (this.value.room) {
                this._generateSelectableModules(this.value.room)

                if (this.value.module) {
                    this._generateSelectableActions(this.value.module)
                }
            }
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

    changeRoom(id: number) {
        this._generateSelectableModules(id)
        const data: Data = {
            room: id,
        }

        this._emitData(data)
    }

    changeDeviceModule(id: number) {
        this._generateSelectableActions(id)
        const data: Data = {
            room: this.value.room,
            module: id,
        }

        this._emitData(data)
    }

    changeDeviceAction(action: string) {
        const data: Data = {
            ...this.value,
            action,
        }

        this._emitData(data)
    }

    private _emitData(data: Data) {
        this.onValidate.emit(this._validate(data))
        this.onChange.emit(data)
    }

    private _validate(data: Data): Validation {
        return {isValid: ['room', 'module', 'action'].every((field) => {
            this.validation[field] = !!data[field]
            return data[field]
        })}
    }

    private _generateSelectableModules(roomId: number) {
        this.selectableModules = this.devices.reduce((modules: Module[], device: Device) => {
            if (device.room.id === roomId) {
                modules = modules.concat(device.modules)
            }

            return modules
        }, [])
    }

    private _generateSelectableActions(moduleId: number) {
        const module: Module = this.devices.reduce((module: Module, device: Device) => {
            if (!module) {
                module = device.modules.find((item: Module) => item.module.id === moduleId)
            }

            return module
        }, null)

        this.selectableActions = TriggerControlTypes.reduce((actions: Action[], action) => {
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
}