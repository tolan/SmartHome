import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';

import {SettingsComponent} from './components/settings/settings';

const settingsRoutes: Routes = [
    {
        path: '',
        component: SettingsComponent,
    }
];

@NgModule({
    imports: [
        RouterModule.forChild(settingsRoutes)
    ],
    exports: [
        RouterModule
    ]
})
export class SettingsRoutingModule {}