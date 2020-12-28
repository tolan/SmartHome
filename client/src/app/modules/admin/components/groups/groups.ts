import {Component, OnInit} from '@angular/core';

import {GroupService} from '../../../../services/group';

import {Group} from '../../../../interfaces/group';

@Component({
    selector: 'groups',
    templateUrl: './groups.html',
    styleUrls: ['./groups.less']
})
export class GroupsComponent implements OnInit {

    groups: Group[] = [];

    constructor(private groupService: GroupService) {}

    ngOnInit() {
        this.groupService.getGroups().subscribe((groups: [Group]) => {
            this.groups = groups;
        }, 'GroupsComponent');
    }

    groupRemove(group: Group) {
        if(!group.group.id) {
            this.groups = this.groups.filter((item) => item !== group);
        } else {
            this.groupService.removeGroup(group);
        }
    }

    groupSave(group: Group) {
        this.groupService.saveGroup(group);
    }

    groupAdd() {
        this.groups.push({
            group: {
                id: null,
                name: null,
            },
            users: [],
            permissions: [],
            rooms: [],
        });
    }
}