import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core';

import {Control} from '../../../../../../../interfaces/control';
import {Module} from '../../../../../../../interfaces/module';

@Component({
    selector: 'fade',
    templateUrl: './fade.html',
    styleUrls: ['./fade.less']
})
export class FadeComponent implements OnInit {

    @Input('control') private control: Control;
    @Input('module') private module: Module;
    @Output('onChange') onChange = new EventEmitter<Control>();
    @Output('onEdit') onEdit = new EventEmitter<Control>();

    public max: number;
    public data: {
        value: number,
        running: boolean,
        delay: {
            value: number,
            unit: string,
        },
    } = {
            value: 0,
            running: false,
            delay: {
                value: 1,
                unit: 'min',
            },
        }

    ngOnInit() {
        const controlData = this.control.control.controlData || {};
        const delayData = controlData.delay || {};
        this.data = {
            value: controlData.value || 0,
            running: controlData.running || false,
            delay: {
                value: delayData.value || 1,
                unit: delayData.unit || 'min',
            }
        }
        this.max = Math.pow(2, this.module.module.settingsData.resolution || 8) - 1;
    }

    onStop() {
        const control = this._getControl(false);
        this.onChange.emit(control);
    }

    onStart() {
        const control = this._getControl(true);
        this.onChange.emit(control);
    }

    changeValue(value: {value: number}) {
        this.data.value = value.value;
        const control = this._getControl(this.data.running);
        this.onEdit.emit(control);
    }

    changeDelayValue(value: {value: number}) {
        this.data.delay.value = value.value;
        const control = this._getControl(this.data.running);
        this.onEdit.emit(control);
    }

    private _getControl(running: boolean): Control {
        return {
            control: {
                ...this.control.control,
                controlData: {
                    ...this.control.control.controlData,
                    ...this.data,
                    running: running,
                }
            }
        };
    }
}