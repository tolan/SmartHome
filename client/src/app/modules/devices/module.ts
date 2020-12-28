import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ReactiveFormsModule} from '@angular/forms';

import {MatSidenavModule} from '@angular/material/sidenav';
import {MatListModule} from '@angular/material/list';
import {MatCardModule} from '@angular/material/card';
import {MatSlideToggleModule} from '@angular/material/slide-toggle';
import {MatSliderModule} from '@angular/material/slider';
import {MatDividerModule} from '@angular/material/divider';
import {MatButtonModule} from '@angular/material/button';
import {MatInputModule} from '@angular/material/input';
import {MatFormFieldModule} from '@angular/material/form-field';
import {MatIconModule} from '@angular/material/icon';
import {DevicesRoutingModule} from './routes';

import {RootComponent} from './components/root/root';
import {RoomsComponent} from './components/rooms/rooms';
import {DevicesComponent} from './components/devices/devices';
import {ModuleComponent} from './components/devices/module/module';
import {FadeComponent} from './components/devices/module/control/fade/fade';
import {PwmComponent} from './components/devices/module/control/pwm/pwm';
import {SwitchComponent} from './components/devices/module/control/switch/switch';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        DevicesRoutingModule,
        MatSidenavModule,
        MatListModule,
        MatCardModule,
        MatSlideToggleModule,
        MatSliderModule,
        MatDividerModule,
        MatButtonModule,
        MatInputModule,
        MatFormFieldModule,
        MatIconModule,
    ],
    declarations: [
        RootComponent,
        RoomsComponent,
        DevicesComponent,
        ModuleComponent,
        FadeComponent,
        PwmComponent,
        SwitchComponent,
    ],
})
export class DevicesModule {}