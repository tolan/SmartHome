import {NgModule} from '@angular/core';

import {PluckPipe} from '../pipes/pluck';
import {JoinPipe} from '../pipes/join';
import {HoursMinutesSeconds} from '../pipes/hoursMinutesSeconds';

@NgModule({
    declarations: [
        PluckPipe,
        JoinPipe,
        HoursMinutesSeconds,
    ],
    exports: [
        PluckPipe,
        JoinPipe,
        HoursMinutesSeconds,
    ]
})
export class PipesModule {}