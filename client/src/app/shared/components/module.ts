import {NgModule} from '@angular/core'
import {CommonModule} from '@angular/common'
import {ReactiveFormsModule} from '@angular/forms'

import {MatButtonModule} from '@angular/material/button'
import {MatIconModule} from '@angular/material/icon'
import {MatMenuModule} from '@angular/material/menu'
import {MatFormFieldModule} from '@angular/material/form-field'
import {MatInputModule} from '@angular/material/input'

import {AddMenuComponent} from './add-menu/component'
import {TimePickerComponent} from './time-picker/component'

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,

        MatButtonModule,
        MatIconModule,
        MatMenuModule,
        MatFormFieldModule,
        MatInputModule,
    ],
    declarations: [
        AddMenuComponent,
        TimePickerComponent,
    ],
    exports: [
        AddMenuComponent,
        TimePickerComponent,
    ],
})
export class ComponentsModule { }