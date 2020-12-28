import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ReactiveFormsModule} from '@angular/forms';

import {MatSidenavModule} from '@angular/material/sidenav';
import {MatListModule} from '@angular/material/list';
import {MatDividerModule} from '@angular/material/divider';
import {MatSelectModule} from '@angular/material/select';
import {MatButtonModule} from '@angular/material/button';
import {MatIconModule} from '@angular/material/icon';
import {MatFormFieldModule} from '@angular/material/form-field';
import {MatInputModule} from '@angular/material/input';
import {MatTabsModule} from '@angular/material/tabs';
import {MatCheckboxModule} from '@angular/material/checkbox';
import {MatTooltipModule} from '@angular/material/tooltip';

import {PipesModule} from '../pipes';

import {RootComponent} from './components/root/root';
import {UsersComponent} from './components/users/users';
import {UsersRowComponent} from './components/users/row/row';
import {UsersRowHeaderComponent} from './components/users/row-header/header';
import {GroupsComponent} from './components/groups/groups';
import {GroupsRowComponent} from './components/groups/row/row';
import {GroupsRowHeaderComponent} from './components/groups/row-header/header';
import {PermissionsComponent} from './components/permissions/permissions';
import {PermissionsRowComponent} from './components/permissions/row/row';
import {PermissionsRowHeaderComponent} from './components/permissions/row-header/header';
import {RoomsComponent} from './components/rooms/rooms';
import {RoomsRowComponent} from './components/rooms/row/row';
import {RoomsRowHeaderComponent} from './components/rooms/row-header/header';
import {DevicesComponent} from './components/devices/devices';
import {DeviceRowComponent} from './components/devices/device/device';
import {DevicesRowModuleComponent} from './components/devices/device/module/module';
import {DevicesRowModuleControlComponent} from './components/devices/device/module/control/control';
import {FirmwaresComponent} from './components/firmwares/firmwares';
import {FirmwaresRowComponent} from './components/firmwares/row/row';
import {FirmwaresRowHeaderComponent} from './components/firmwares/row-header/header';

import {AdminRoutingModule} from './routes';


@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        AdminRoutingModule,
        MatSidenavModule,
        MatListModule,
        MatDividerModule,
        MatSelectModule,
        MatButtonModule,
        MatIconModule,
        MatFormFieldModule,
        MatInputModule,
        MatTabsModule,
        MatCheckboxModule,
        MatTooltipModule,
        PipesModule,
    ],
    declarations: [
        RootComponent,
        UsersComponent,
        UsersRowComponent,
        UsersRowHeaderComponent,
        GroupsComponent,
        GroupsRowComponent,
        GroupsRowHeaderComponent,
        PermissionsComponent,
        PermissionsRowComponent,
        PermissionsRowHeaderComponent,
        RoomsComponent,
        RoomsRowComponent,
        RoomsRowHeaderComponent,
        DevicesComponent,
        DeviceRowComponent,
        DevicesRowModuleComponent,
        DevicesRowModuleControlComponent,
        FirmwaresComponent,
        FirmwaresRowComponent,
        FirmwaresRowHeaderComponent,
    ],
})
export class AdminModule {}