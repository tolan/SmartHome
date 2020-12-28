import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {ContainerFactory, Container} from '../lib/container';
import {Mediator, buildKeys, EventGroup, EventAction} from '../lib/mediator';

import {Group} from '../interfaces/group';

@Injectable({
    providedIn: 'root'
})
export class GroupService {

    private container: Container;

    constructor(private http: HttpClient, private mediator: Mediator, containerFactory: ContainerFactory) {
        this.container = containerFactory.getContainer('GroupService');
        this._fetchGroups();
        this.mediator.on(buildKeys(EventGroup.ROOM), () => this._fetchGroups());
        this.mediator.on(buildKeys(EventGroup.PERMISSION), () => this._fetchGroups());
    }

    getGroups() {
        return this.container.get('groups');
    }

    saveGroup(group: Group) {
        if (!group.group.id) {
            this.http.put('/api/0/group', {...group}).subscribe(() => {
                this._fetchGroups();
                this.mediator.emit(buildKeys(EventGroup.GROUP, EventAction.create), group);
            });
        } else {
            this.http.post('/api/0/group', {...group}).subscribe(() => {
                this._fetchGroups();
                this.mediator.emit(buildKeys(EventGroup.GROUP, EventAction.update), group);
            });
        }
    }

    removeGroup(group: Group) {
        this.http.delete('/api/0/group/' + group.group.id).subscribe(() => {
            this._fetchGroups();
            this.mediator.emit(buildKeys(EventGroup.GROUP, EventAction.remove), group);
        });
    }

    private _fetchGroups() {
        this.http.get('/api/0/groups').subscribe((groups: [Group]) => {
            this.container.set('groups', groups);
        });
    }
}
