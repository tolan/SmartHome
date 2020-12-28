import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core';
import {FormBuilder} from '@angular/forms';

import {Permission} from '../../../../../interfaces/permission';

@Component({
    selector: 'permissions-row',
    templateUrl: './row.html',
    styleUrls: ['./row.less']
})
export class PermissionsRowComponent implements OnInit {

    @Input('permission') public perm: Permission;
    @Output('onSave') onSave = new EventEmitter<Permission>();

    public permForm: any;

    public isEditing: boolean = false;

    constructor(private formBuilder: FormBuilder) {}

    ngOnInit() {
        this.permForm = this.formBuilder.group({
            name: this.perm.permission.name,
        });
    }

    edit() {
        this.isEditing = true;
    }

    save() {
        const perm: Permission = {permission: {...this.perm.permission, ...this.permForm.value}};
        this.onSave.emit(perm);
    }

    cancel() {
        this.isEditing = false;
    }

}