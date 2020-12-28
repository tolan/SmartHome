import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core';

import {Control} from '../../../../../../../interfaces/control';

@Component({
    selector: 'switch',
    templateUrl: './switch.html',
    styleUrls: ['./switch.less']
})
export class SwitchComponent implements OnInit {

    @Input('control') private control: Control;
    @Input('simple') public simple: boolean = false;
    @Output('onChange') onChange = new EventEmitter<Control>();
    @Output('onEdit') onEdit = new EventEmitter<Control>();

    public data: {
        value: boolean,
        delay: {
            value: number,
            unit: string,
        },
    } = {
            value: false,
            delay: {
                value: 0,
                unit: 'sec',
            },
        }

    ngOnInit() {
        const controlData = this.control.control.controlData || {};
        const delayData = controlData.delay || {};
        this.data = {
            value: controlData.value,
            delay: {
                value: delayData.value,
                unit: delayData.unit,
            }
        }
    }

    changeValue(value: {checked: boolean}) {
        this.data.value = value.checked;
        this.onChange.emit(this._getControl());
    }

    changeDelayValue(value: {value: number}) {
        this.data.delay.value = value.value;
        this.onEdit.emit(this._getControl());
    }

    changeDelayUnit(value: {checked: boolean}) {
        this.data.delay.unit = value.checked ? 'min' : 'sec';
        this.onEdit.emit(this._getControl());
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