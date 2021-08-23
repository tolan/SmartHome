import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core'
import {FormBuilder} from '@angular/forms'

import {ModuleTypeName} from '../../../../../enums/moduleType'

import {FirmwareService} from '../../../../../services/firmware'
import {RoomService} from '../../../../../services/room'

import {Device} from '../../../../../interfaces/device'
import {Firmware} from '../../../../../interfaces/firmware'
import {Room} from '../../../../../interfaces/room'
import {Module} from '../../../../../interfaces/module'

@Component({
    selector: 'device-row',
    templateUrl: './device.html',
    styleUrls: ['./device.less']
})
export class DeviceRowComponent implements OnInit {

    @Input('device') public device: Device
    @Output('onSave') onSave = new EventEmitter<Device>()
    @Output('onRemove') onRemove = new EventEmitter<Device>()
    @Output('onRestart') onRestart = new EventEmitter<Device>()

    public ModuleTypes = Object.entries(ModuleTypeName).map(([id, title]) => ({id, title}))

    public basicForm: any
    public firmwareForm: any
    public roomForm: any

    public firmwares: Firmware[] = []
    public rooms: Room[] = []

    public isEditing: boolean = false
    public isRemoving: boolean = false

    public modules: any[] = []

    constructor(
        private firmwareService: FirmwareService,
        private roomService: RoomService,
        private formBuilder: FormBuilder
    ) {}

    ngOnInit() {
        this.modules = [].concat(this.device.modules.map((item: Module) => ({...item})))

        this.firmwareService.getFirmwares().subscribe((firmwares: [Firmware]) => {
            this.firmwares = firmwares
        }, 'DeviceRowComponent')

        this.roomService.getRooms().subscribe((rooms: [Room]) => {
            this.rooms = rooms
        }, 'DeviceRowComponent')

        this.basicForm = this.formBuilder.group({
            name: this.device.device.name,
        })
        this.firmwareForm = this.formBuilder.group({
            firmware: this.device.firmware,
        })
        this.roomForm = this.formBuilder.group({
            room: this.device.room,
        })
    }

    selectComparator(a: {id: string}, b: {id: string}) {
        return (a || {id: null}).id === (b || {id: null}).id
    }

    addModule(type: string) {
        this.modules = this.modules.concat([{
            module: {type},
            controls: [],
        }])
    }

    removeModule(module: any) {
        this.modules = this.modules.filter((item) => item !== module)
    }

    changeModule(event: {module: Module, changed: Module}) {
        this.modules = this.modules.map((item) => {
            if (item === event.module) {
                Object.assign(item, event.changed)
            }

            return item
        })
    }

    edit() {
        this.isRemoving = false
        this.isEditing = true
    }

    remove() {
        this.isRemoving = true
        this.isEditing = false
    }

    save() {
        if (this.isRemoving) {
            this.onRemove.emit(this.device)
        } else {
            const device = {
                device: {
                    ...this.device.device,
                    ...this.basicForm.value
                },
                ...this.firmwareForm.value,
                ...this.roomForm.value,
                modules: [].concat(this.modules),
            }
            this.onSave.emit(device)
        }
    }

    cancel() {
        if (!this.device.device.id) {
            this.onRemove.emit(this.device)
        } else {
            this.isEditing = false
            this.isRemoving = false
        }
    }

    restart() {
        this.onRestart.emit(this.device)
    }
}