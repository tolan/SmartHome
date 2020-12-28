import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core';
import {FormBuilder} from '@angular/forms';

import {GroupService} from '../../../../../services/group';
import {DeviceService} from '../../../../../services/device';

import {Room} from '../../../../../interfaces/room';
import {Group} from '../../../../../interfaces/group';
import {Device} from '../../../../../interfaces/device';

@Component({
    selector: 'rooms-row',
    templateUrl: './row.html',
    styleUrls: ['./row.less']
})
export class RoomsRowComponent implements OnInit {

    @Input('room') public room: Room;
    @Output('onSave') onSave = new EventEmitter<Room>();
    @Output('onRemove') onRemove = new EventEmitter<Room>();

    public groups: Group[] = [];
    public devices: Device[] = [];

    public roomForm: any;
    public extendedForm: any;

    public isEditing: boolean = false;
    public isRemoving: boolean = false;

    constructor(
        private groupService: GroupService,
        private deviceService: DeviceService,
        private formBuilder: FormBuilder
    ) {}

    ngOnInit() {
        if (!this.room.room.id) {
            this.edit();
        }

        this.groupService.getGroups().subscribe((groups: [Group]) => {
            this.groups = groups;
        }, 'RoomsRowComponent');

        this.deviceService.getDevices().subscribe((devices: [Device]) => {
            this.devices = devices;
        }, 'RoomsRowComponent');

        this.roomForm = this.formBuilder.group({
            name: this.room.room.name,
        });

        this.extendedForm = this.formBuilder.group({
            groups: [this.room.groups],
            devices: [this.room.devices],
        });
    }

    itemComparator(a: {id: string}, b: {id: string}) {
        return (a || {}).id === (b || {}).id;
    }

    edit() {
        this.isRemoving = false;
        this.isEditing = true;
    }

    remove() {
        this.isRemoving = true;
        this.isEditing = false;
    }

    save() {
        if (this.isRemoving) {
            this.onRemove.emit(this.room);
        } else {
            const room = {
                room: {
                    ...this.room.room, ...this.roomForm.value
                },
                ...this.extendedForm.value
            };
            this.onSave.emit(room);
        }
    }

    cancel() {
        if (!this.room.room.id) {
            this.onRemove.emit(this.room);
        } else {
            this.isEditing = false;
            this.isRemoving = false;
        }
    }

}