import {Component, EventEmitter, Input, Output, OnChanges} from '@angular/core';

import {Output as Out} from '../../../../../../interfaces/task';

interface Set {
    add: (value: any) => this,
}

@Component({
    selector: 'output-selector',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class OutputSelectorComponent implements OnChanges {

    @Input('output') public output: Out
    @Output('onSelect') onSelect = new EventEmitter<string>();

    public selectable: string[] = [];

    ngOnChanges() {
        this.selectable = [...
            []
                .concat(this.output.defaults, this.output.custom)
                .reduce((acc: Set, {key}: {key: string}) => (acc.add(key)), new Set())
        ]
    }

    onClick(key: string) {
        this.onSelect.emit(key);
    }
}