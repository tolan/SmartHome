import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';

import {RootComponent} from './components/root/root';
import {UsersComponent} from './components/users/users';
import {GroupsComponent} from './components/groups/groups';
import {PermissionsComponent} from './components/permissions/permissions';
import {RoomsComponent} from './components/rooms/rooms';
import {DevicesComponent} from './components/devices/devices';
import {FirmwaresComponent} from './components/firmwares/firmwares';
import {ProcessesComponent} from './components/processes/processes';

const adminRoutes: Routes = [
    {
        path: '',
        component: RootComponent,
        children: [
            {
                path: 'users',
                component: UsersComponent,
                data: {
                    title: 'Uživatelé',
                },
            },
            {
                path: 'groups',
                component: GroupsComponent,
                data: {
                    title: 'Skupiny',
                },
            },
            {
                path: 'permissions',
                component: PermissionsComponent,
                data: {
                    title: 'Oprávnění',
                },
            },
            {
                path: 'rooms',
                component: RoomsComponent,
                data: {
                    title: 'Místnosti',
                },
            },
            {
                path: 'devices',
                component: DevicesComponent,
                data: {
                    title: 'Zařízení',
                },
            },
            {
                path: 'firmwares',
                component: FirmwaresComponent,
                data: {
                    title: 'Firmware',
                },
            },
            {
                path: 'processes',
                component: ProcessesComponent,
                data: {
                    title: 'Procesy',
                },
            },
        ]
    }
];

@NgModule({
    imports: [
        RouterModule.forChild(adminRoutes)
    ],
    exports: [
        RouterModule
    ]
})
export class AdminRoutingModule {}