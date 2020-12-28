import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {ContainerFactory, Container} from '../lib/container';
import {Mediator, buildKeys, EventGroup, EventAction} from '../lib/mediator';

import {Permission} from '../interfaces/permission';

@Injectable({
    providedIn: 'root'
})
export class PermissionService {

    private container: Container;

    constructor(private http: HttpClient, private mediator: Mediator, containerFactory: ContainerFactory) {
        this.container = containerFactory.getContainer('PermissionService');
        this._fetchPermissions();
    }

    getPermissions() {
        return this.container.get('permissions');
    }

    savePermission(permission: Permission) {
        this.http.post('/api/0/permission', {...permission}).subscribe(() => {
            this._fetchPermissions();
            this.mediator.emit(buildKeys(EventGroup.PERMISSION, EventAction.update), permission);
        });
    }

    private _fetchPermissions() {
        this.http.get('/api/0/permissions').subscribe((permissions: [Permission]) => {
            this.container.set('permissions', permissions);
        });
    }
}
