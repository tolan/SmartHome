import {Component, OnInit} from '@angular/core';

import {PermissionService} from '../../../../services/permission';

import {Permission} from '../../../../interfaces/permission';

@Component({
    selector: 'permissions',
    templateUrl: './permissions.html',
    styleUrls: ['./permissions.less']
})
export class PermissionsComponent implements OnInit {

    perms: Permission[] = [];

    constructor(private permissionService: PermissionService) {}

    ngOnInit() {
        this.permissionService.getPermissions().subscribe((perms: [Permission]) => {
            this.perms = perms;
        }, 'PermissionsComponent');
    }

    permSave(perm: Permission) {
        this.permissionService.savePermission(perm);
    }

}