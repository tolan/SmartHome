import {NgModule} from '@angular/core'
import {CommonModule} from '@angular/common'
import {ReactiveFormsModule} from '@angular/forms'

import {MatSidenavModule} from '@angular/material/sidenav'
import {MatGridListModule} from '@angular/material/grid-list'
import {MatListModule} from '@angular/material/list'
import {MatInputModule} from '@angular/material/input'
import {MatFormFieldModule} from '@angular/material/form-field'
import {MatButtonModule} from '@angular/material/button'
import {MatIconModule} from '@angular/material/icon'
import {MatMenuModule} from '@angular/material/menu'
import {MatSelectModule} from '@angular/material/select'
import {MatSlideToggleModule} from '@angular/material/slide-toggle'
import {MatCheckboxModule} from '@angular/material/checkbox'
import {MatDividerModule} from '@angular/material/divider'
import {MatButtonToggleModule} from '@angular/material/button-toggle'
import {MatTabsModule} from '@angular/material/tabs'
import {MatSliderModule} from '@angular/material/slider'
import {MatTooltipModule} from '@angular/material/tooltip'
import {MatTableModule} from '@angular/material/table'
import {MatPaginatorModule} from '@angular/material/paginator'

import {ComponentsModule} from '../../shared/components/module'

import {SchedulerRoutingModule} from './routes'

import {RootComponent} from './components/root/root'
import {ListComponent} from './components/list/list'
import {EditComponent} from './components/edit/edit'

import {TriggersComponent} from './components/edit/triggers/triggers'
import {TriggerComponent} from './components/edit/triggers/trigger/trigger'
import {TriggerEventComponent} from './components/edit/triggers/trigger/event/component'
import {TriggerEventDeviceComponent} from './components/edit/triggers/trigger/event/device/component'
import {TriggerEventSunComponent} from './components/edit/triggers/trigger/event/sun/component'
import {TriggerTimeComponent} from './components/edit/triggers/trigger/time/component'
import {TriggerTimeDailyComponent} from './components/edit/triggers/trigger/time/daily/component'
import {TriggerTimeWeeklyComponent} from './components/edit/triggers/trigger/time/weekly/component'
import {TriggerTimeMonthlyComponent} from './components/edit/triggers/trigger/time/monthly/component'
import {TriggerMQTTComponent} from './components/edit/triggers/trigger/mqtt/component'

import {OutputEditComponent} from './components/edit/output/edit/component'
import {OutputSelectorComponent} from './components/edit/output/selector/component'
import {OutputSelectComponent} from './components/edit/output/select/component'

import {ConditionsComponent} from './components/edit/conditions/conditions'
import {OrConditionComponent} from './components/edit/conditions/condition/or/component'
import {PingConditionComponent} from './components/edit/conditions/condition/ping/component'
import {TimeConditionComponent} from './components/edit/conditions/condition/time/component'
import {ValueConditionComponent} from './components/edit/conditions/condition/value/component'
import {LastRunConditionComponent} from './components/edit/conditions/condition/lastRun/component'

import {ActionsComponent} from './components/edit/actions/component'
import {ActionComponent} from './components/edit/actions/action/component'
import {DeviceActionComponent} from './components/edit/actions/action/device/component'
import {HTTPActionComponent} from './components/edit/actions/action/http/component'
import {MQTTActionComponent} from './components/edit/actions/action/mqtt/component'

import {LogsComponent} from './components/edit/logs/component'


@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        SchedulerRoutingModule,
        MatSidenavModule,
        MatGridListModule,
        MatListModule,
        MatInputModule,
        MatFormFieldModule,
        MatButtonModule,
        MatIconModule,
        MatMenuModule,
        MatSelectModule,
        MatSlideToggleModule,
        MatCheckboxModule,
        MatDividerModule,
        MatButtonToggleModule,
        MatTabsModule,
        MatSliderModule,
        MatTooltipModule,
        MatTableModule,
        MatPaginatorModule,
        ComponentsModule,
    ],
    declarations: [
        RootComponent,
        ListComponent,
        EditComponent,
        TriggersComponent,
        TriggerComponent,
        TriggerEventComponent,
        TriggerEventDeviceComponent,
        TriggerEventSunComponent,
        TriggerMQTTComponent,
        OutputEditComponent,
        OutputSelectorComponent,
        OutputSelectComponent,
        TriggerTimeComponent,
        TriggerTimeDailyComponent,
        TriggerTimeWeeklyComponent,
        TriggerTimeMonthlyComponent,
        ConditionsComponent,
        OrConditionComponent,
        PingConditionComponent,
        TimeConditionComponent,
        ValueConditionComponent,
        LastRunConditionComponent,
        ActionsComponent,
        ActionComponent,
        DeviceActionComponent,
        HTTPActionComponent,
        MQTTActionComponent,
        LogsComponent,
    ],
})
export class SchedulerModule {}