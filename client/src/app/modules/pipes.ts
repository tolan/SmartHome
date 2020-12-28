import {NgModule} from '@angular/core';

import {PluckPipe} from '../pipes/pluck';
import {JoinPipe} from '../pipes/join';

@NgModule({
    declarations: [
        PluckPipe,
        JoinPipe,
    ],
    exports: [
        PluckPipe,
        JoinPipe,
    ]
})
export class PipesModule {}