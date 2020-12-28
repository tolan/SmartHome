import {Component} from '@angular/core';
import {FormBuilder} from '@angular/forms';

import {UserService} from '../../services/user';

@Component({
    selector: 'login',
    templateUrl: './login.html',
    styleUrls: ['./login.less']
})
export class LoginComponent {

    public loginForm: any;

    constructor(private userService: UserService, private formBuilder: FormBuilder) {
        this.loginForm = this.formBuilder.group({
            username: '',
            password: ''
        });
    }

    onSubmit(data: {username: string, password: string}) {
        const {username, password} = data;
        this.userService.login(username, password);
    }
}