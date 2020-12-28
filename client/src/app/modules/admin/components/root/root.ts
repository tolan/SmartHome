import {Component} from '@angular/core';

import {UserService} from '../../../../services/user';
import {User} from '../../../../interfaces/user';

import {Permissions} from '../../../../enums/permissions';

@Component({
    selector: 'root',
    templateUrl: './root.html',
    styleUrls: ['./root.less']
})
export class RootComponent {

    public hidden: boolean = true;

    constructor(private userService: UserService) {}

    ngOnInit() {
        this.userService.getUser().subscribe((user?: User) => {
            if (user && user.permissions.find((perm: {type: string}) => perm.type === Permissions.TYPE_SECTION_ADMIN)) {
                this.hidden = false;
            }
        })
    }

}