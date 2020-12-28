import {Injectable} from '@angular/core';
import {HttpClient, HttpHeaders} from '@angular/common/http';
import {ContainerFactory, Container} from '../lib/container';

import {User} from '../interfaces/user';

@Injectable({
    providedIn: 'root'
})
export class UserService {

    private container: Container;

    constructor(private http: HttpClient, containerFactory: ContainerFactory) {
        this.container = containerFactory.getContainer('UserService');
        this._fetchUser();
    }

    getUser() {
        return this.container.get('user');
    }

    login(username: string, password: string) {
        this.http.post('/api/0/user/login', {username, password}).subscribe(() => this._fetchUser());
        return this;
    }

    logout() {
        this.http.post('/api/0/user/logout', {}).subscribe(() => {
            this.container.set('user', null, true);
            this._fetchUser();
        });
        return this;
    }

    save(user: {}) {
        return this.http.post('/api/0/user/self', user).subscribe(() => this._fetchUser());
    }

    generateApiToken(user: User) {
        return this.http.post('/api/0/user/' + user.user.id + '/apiToken/generate', {}).subscribe(() => this._fetchUser());
    }

    _fetchUser() {
        const getUser = (token: string = '') => {
            const headers = new HttpHeaders().set('X-User-Login-Token', token);
            this.http.get('/api/0/user', {headers}).subscribe((user?: User) => {
                this.container.set('user', user, true);
            });
        }

        if (this.container.has('user')) {
            this.container.get('user').subscribe((user?: User) => {
                this.container.get('user').unsubscribe('fetchUser');
                getUser(user && user.user.token || '');
            }, 'fetchUser');
        } else {
            getUser('');
        }
    }
}
