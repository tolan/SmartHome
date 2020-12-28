import {Component, OnInit, OnDestroy} from '@angular/core';

import {UserService} from '../../../../services/user';

import {User} from '../../../../interfaces/user';

import {Permissions} from '../../../../enums/permissions';

@Component({
    selector: 'root',
    templateUrl: './root.html',
    styleUrls: ['./root.less']
})
export class RootComponent implements OnInit, OnDestroy {

    public hidden: boolean = true;

    constructor(private userService: UserService) {
    }

    ngOnInit() {
        this.userService.getUser().subscribe((user?: User) => {
            if (user && user.permissions.find((perm: {type: string}) => perm.type === Permissions.TYPE_SECTION_DEVICES)) {
                this.hidden = false;
            }
        }, 'DevicesRootComponent');
    }

    ngOnDestroy() {
        this.userService.getUser().unsubscribe('DevicesRootComponent');
    }

}