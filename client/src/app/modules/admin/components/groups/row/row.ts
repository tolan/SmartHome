import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core';
import {FormBuilder} from '@angular/forms';

import {PermissionService} from '../../../../../services/permission';
import {RoomService} from '../../../../../services/room';

import {Group} from '../../../../../interfaces/group';
import {Permission} from '../../../../../interfaces/permission';
import {Room} from '../../../../../interfaces/room';

@Component({
    selector: 'groups-row',
    templateUrl: './row.html',
    styleUrls: ['./row.less']
})
export class GroupsRowComponent implements OnInit {

    @Input('group') public group: Group;
    @Output('onSave') onSave = new EventEmitter<Group>();
    @Output('onRemove') onRemove = new EventEmitter<Group>();

    public permissions: Permission[] = [];
    public rooms: Room[] = [];

    public groupForm: any;
    public extendedForm: any;

    public isEditing: boolean = false;
    public isRemoving: boolean = false;

    constructor(
        private permService: PermissionService,
        private roomService: RoomService,
        private formBuilder: FormBuilder
    ) {}

    ngOnInit() {
        if (!this.group.group.id) {
            this.edit();
        }

        this.permService.getPermissions().subscribe((permissions: [Permission]) => {
            this.permissions = permissions;
        }, 'GroupsRowComponent');

        this.roomService.getRooms().subscribe((rooms: [Room]) => {
            this.rooms = rooms;
        }, 'GroupsRowComponent');

        this.groupForm = this.formBuilder.group({
            name: this.group.group.name,
        });
        this.extendedForm = this.formBuilder.group({
            permissions: [this.group.permissions],
            rooms: [this.group.rooms],
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
            this.onRemove.emit(this.group);
        } else {
            const group = {
                group: {
                    ...this.group.group,
                    ...this.groupForm.value
                },
                ...this.extendedForm.value
            };
            this.onSave.emit(group);
        }
    }

    cancel() {
        if (!this.group.group.id) {
            this.onRemove.emit(this.group);
        } else {
            this.isEditing = false;
            this.isRemoving = false;
        }
    }

}