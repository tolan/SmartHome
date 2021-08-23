import {NgModule} from '@angular/core';

import {PluckPipe} from './pluck';
import {JoinPipe} from './join';
import {HoursMinutesSeconds} from './hoursMinutesSeconds';

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