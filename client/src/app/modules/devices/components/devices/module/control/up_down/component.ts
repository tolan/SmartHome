import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core';

import {Control} from '../../../../../../../interfaces/control';

import {UpDownType} from '../../../../../../../enums/controlType';

@Component({
    selector: 'up-down',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class UpDownComponent implements OnInit {

    @Input('control') private control: Control;
    @Input('simple') public simple: boolean = false;
    @Output('onChange') onChange = new EventEmitter<Control>();
    @Output('onEdit') onEdit = new EventEmitter<Control>();

    public data: {
        value: string,
    } = {
        value: UpDownType.STOP,
    }

    ngOnInit() {
        const controlData = this.control.control.controlData || {};
        this.data = {
            value: controlData.value,
        }
    }

    up () {
        this.data.value = UpDownType.UP;
        this.onChange.emit(this._getControl());
    }

    down () {
        this.data.value = UpDownType.DOWN;
        this.onChange.emit(this._getControl());
    }
    
    stop () {
        this.data.value = UpDownType.STOP;
        this.onChange.emit(this._getControl());
    }

            private _getControl(): Control {
        return {
            control: {
                ...this.control.control,
                controlData: {
                    ...this.control.control.controlData,
                    ...this.data,
                }
            }
        };
    }
}