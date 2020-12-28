import {Component, OnInit} from '@angular/core';
import {FormBuilder} from '@angular/forms';

import {UserService} from '../../../../services/user';

import {User} from '../../../../interfaces/user';

@Component({
    selector: 'settings',
    templateUrl: './settings.html',
    styleUrls: ['./settings.less']
})
export class SettingsComponent implements OnInit {

    public user: User = null;

    public isEditing: boolean = false;

    public userForm: any;

    constructor(private userService: UserService, private formBuilder: FormBuilder) {}

    ngOnInit() {
        this.userService.getUser().subscribe((user: User) => {
            this.user = user;
            this.cancel();
        }, 'SettingsComponent');

        this.userForm = this.formBuilder.group({
            oldPass: null,
            newPass: null,
            newPassRepeat: null,
        });
    }

    generateApiToken() {
        this.userService.generateApiToken(this.user);
    }

    edit() {
        this.isEditing = true;
    }

    save() {
        const user = {
            ...this.user.user,
            ...this.userForm.value,
        }

        this.userService.save(user);
    }

    cancel() {
        this.isEditing = false;
    }
}