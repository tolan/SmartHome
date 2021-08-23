import {Component, OnInit} from '@angular/core';
import {Router} from '@angular/router';

import {UserService} from '../../../services/user';

import {User} from '../../../interfaces/user';

import {Sections, SectionInterface} from '../../../enums/sections';

@Component({
    selector: 'navigation',
    templateUrl: './navigation.html',
    styleUrls: ['./navigation.less']
})
export class NavigationComponent implements OnInit {

    public sections: SectionInterface[];

    constructor(private router: Router, private userService: UserService) {
        this.sections = [];
    }

    ngOnInit() {
        this.userService.getUser().subscribe((user?: User) => {
            if (user && user.permissions.length) {
                this.sections = Object.keys(Sections).reduce((acc: SectionInterface[], permType: string) => {
                    if (user.permissions.some((perm: {type: string}) => perm.type === permType)) {
                        acc.push(Sections[permType]);
                    }

                    return acc
                }, []);
            } else {
                this.sections = [];
            }
        });
    }

    onLogout() {
        this.userService.logout();
        this.router.navigate(['/login']);
    }

}