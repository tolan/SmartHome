import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {ContainerFactory, Container} from '../lib/container';
import {Mediator, buildKeys, EventGroup, EventAction} from '../lib/mediator';

import {User} from '../interfaces/user';

@Injectable({
    providedIn: 'root'
})
export class UsersService {

    private container: Container;

    constructor(private http: HttpClient, private mediator: Mediator, containerFactory: ContainerFactory) {
        this.container = containerFactory.getContainer('UsersService');
        this._fetchUsers();
        this.mediator.on(buildKeys(EventGroup.GROUP), () => this._fetchUsers());
    }

    getUsers() {
        return this.container.get('users');
    }

    removeUser(user: User) {
        this.http.delete('/api/0/user/' + user.user.id).subscribe(() => {
            this._fetchUsers();
            this.mediator.emit(buildKeys(EventGroup.USER, EventAction.remove), user);
        });
    }

    saveUser(user: User) {
        if (!user.user.id) {
            this.http.put('/api/0/user', {...user}).subscribe(() => {
                this._fetchUsers();
                this.mediator.emit(buildKeys(EventGroup.USER, EventAction.create), user);
            });
        } else {
            this.http.post('/api/0/user', {...user}).subscribe(() => {
                this._fetchUsers();
                this.mediator.emit(buildKeys(EventGroup.USER, EventAction.update), user);
            });
        }
    }

    private _fetchUsers() {
        this.http.get('/api/0/users').subscribe((users: [User]) => {
            this.container.set('users', users);
        });
    }
}
