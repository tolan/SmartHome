import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core';
import {FormBuilder} from '@angular/forms';

import {GroupService} from '../../../../../services/group';

import {User} from '../../../../../interfaces/user';
import {Group} from '../../../../../interfaces/group';

@Component({
    selector: 'users-row',
    templateUrl: './row.html',
    styleUrls: ['./row.less']
})
export class UsersRowComponent implements OnInit {

    @Input('user') public user: User;
    @Output('onSave') onSave = new EventEmitter<User>();
    @Output('onRemove') onRemove = new EventEmitter<User>();

    public groups: Group[] = [];

    public userForm: any;
    public groupsForm: any;

    public isEditing: boolean = false;
    public isRemoving: boolean = false;

    constructor(private groupService: GroupService, private formBuilder: FormBuilder) {}

    ngOnInit() {
        if (!this.user.user.id) {
            this.edit();
        }

        this.groupService.getGroups().subscribe((groups: [Group]) => {
            this.groups = groups;
        }, 'UsersRowComponent');

        this.userForm = this.formBuilder.group({
            username: this.user.user.username,
            password: '',
        });
        this.groupsForm = this.formBuilder.group({
            groups: [this.user.groups],
        });
    }

    groupComparator(a: {id: string}, b: {id: string}) {
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
            this.onRemove.emit(this.user);
        } else {
            const user = {user: {...this.user.user, ...this.userForm.value}, ...this.groupsForm.value};
            this.onSave.emit(user);
        }
    }

    cancel() {
        if (!this.user.user.id) {
            this.onRemove.emit(this.user);
        } else {
            this.isEditing = false;
            this.isRemoving = false;
        }
    }

}