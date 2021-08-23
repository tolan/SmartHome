import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';

import {LoginComponent} from './modules/root/login/login';
import {NoPermsComponent} from './modules/root/no-perms/no-perms';
import {PageNotFoundComponent} from './modules/root/page-not-found/page-not-found';

const appRoutes: Routes = [
    {path: '', redirectTo: 'login', pathMatch: 'full'},
    {path: 'login', component: LoginComponent},
    {path: 'no-perms', component: NoPermsComponent},
    {
        path: 'admin',
        loadChildren: () => import('./modules/admin/module').then(m => m.AdminModule),
        data: {
            title: 'Administrace',
        },
    },
    {
        path: 'devices',
        loadChildren: () => import('./modules/devices/module').then(m => m.DevicesModule),
        data: {
            title: 'Zařízení',
        },
    },
    {
        path: 'scheduler',
        loadChildren: () => import('./modules/scheduler/module').then(m => m.SchedulerModule),
        data: {
            title: 'Plánovač',
        },
    },
    {
        path: 'settings',
        loadChildren: () => import('./modules/settings/module').then(m => m.SettingsModule),
        data: {
            title: 'Nastavení',
        },
    },
    {path: '**', component: PageNotFoundComponent},
];

@NgModule({
    imports: [
        RouterModule.forRoot(appRoutes, { relativeLinkResolution: 'legacy' }),
    ],
    exports: [
        RouterModule
    ]
})
export class AppRoutingModule {}
