import {Component, OnInit} from '@angular/core';

import {UsersService} from '../../../../services/users';

import {User} from '../../../../interfaces/user';

@Component({
    selector: 'users',
    templateUrl: './users.html',
    styleUrls: ['./users.less']
})
export class UsersComponent implements OnInit {

    users: User[] = [];

    constructor(private usersService: UsersService) {}

    ngOnInit() {
        this.usersService.getUsers().subscribe((users: [User]) => {
            this.users = users;
        }, 'UsersComponent');
    }

    userRemove(user: User) {
        if(!user.user.id) {
            this.users = this.users.filter((us) => us !== user);
        } else {
            this.usersService.removeUser(user);
        }
    }

    userSave(user: User) {
        this.usersService.saveUser(user);
    }

    userAdd() {
        this.users.push({
            user: {
                id: null,
                username: null,
            },
            groups: [],
            permissions: [],
        });
    }
}