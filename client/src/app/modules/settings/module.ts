import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ReactiveFormsModule} from '@angular/forms';

import {PipesModule} from '../pipes';

import {MatButtonModule} from '@angular/material/button';
import {MatIconModule} from '@angular/material/icon';
import {MatFormFieldModule} from '@angular/material/form-field';
import {MatInputModule} from '@angular/material/input';

import {SettingsRoutingModule} from './routes';

import {SettingsComponent} from './components/settings/settings';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        PipesModule,
        SettingsRoutingModule,
        MatButtonModule,
        MatIconModule,
        MatFormFieldModule,
        MatInputModule,
    ],
    declarations: [
        SettingsComponent,
    ],
})
export class SettingsModule { }