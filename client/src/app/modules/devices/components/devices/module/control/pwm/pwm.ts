import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core';
import {FormBuilder} from '@angular/forms';

import {Control} from '../../../../../../../interfaces/control';
import {Module} from '../../../../../../../interfaces/module';

import {Subject} from 'rxjs';
import {debounceTime} from 'rxjs/operators';

@Component({
    selector: 'pwm',
    templateUrl: './pwm.html',
    styleUrls: ['./pwm.less']
})
export class PwmComponent implements OnInit {

    @Input('control') private control: Control;
    @Input('module') private module: Module;
    @Input('simple') public simple: boolean = false;
    @Output('onChange') onChange = new EventEmitter<Control>();
    @Output('onEdit') onEdit = new EventEmitter<Control>();

    public controlForm: any;

    private steps: number[] = [0, 0.1, 0.5, 1, 2, 3, 4, 5, 7.5, 10, 15, 20, 35, 50, 75, 100];

    private subject: any;

    public max: number;
    private data: {
        value: number,
    } = {
            value: 0,
        }

    constructor(private formBuilder: FormBuilder) {
        this.subject = new Subject();
        this.subject.pipe(
            debounceTime(500),
        ).subscribe((value: number) => {
            this.controlForm.value.value = value;
            this._emitChange();
        })
    }

    ngOnInit() {
        this.data.value = this.control.control.controlData.value;
        this.max = Math.pow(2, this.module.module.settingsData.resolution || 8) - 1;

        this.controlForm = this.formBuilder.group({
            value: this.data.value,
        });
    }

    minus() {
        const nearest = this._getNearestStep();
        let index = Math.max(0, this.steps.indexOf(nearest) - 1);

        if (this.controlForm.value.value === Math.round(this.max * this.steps[index] / 100)) {
            index = Math.max(0, index - 1);
        }

        this.controlForm.value.value = Math.round(this.max * this.steps[index] / 100);
        this._emitChange();
    }

    plus() {
        const nearest = this._getNearestStep();
        let index = Math.min(this.steps.length - 1, this.steps.indexOf(nearest));

        if (this.controlForm.value.value === Math.round(this.max * this.steps[index] / 100) && index !== (this.steps.length - 1)) {
            index = Math.min(this.steps[this.steps.length - 1], index + 1);
        }

        this.controlForm.value.value = Math.round(this.max * this.steps[index] / 100);
        this._emitChange();
    }


    changeValue(event: {value: number}) {
        this.controlForm.value.value = event.value !== undefined ? event.value : this.controlForm.value.value;
        this.onEdit.emit(this._getControl());
        this.subject.next(this.controlForm.value.value);
    }

    focus() {
        this.onEdit.emit(this._getControl());
    }

    private _emitChange() {
        this.onChange.emit(this._getControl());
    }

    private _getControl(): Control {
        this.data.value = this.controlForm.value.value;
        return {
            control: {
                ...this.control.control,
                controlData: {
                    ...this.control.control.controlData,
                    ...this.data,
                }
            }
        }
    }

    private _getNearestStep() {
        const current = this.controlForm.value.value;
        return this.steps.reduce((acc: number, value: number) => {
            const next = Math.round((value / 100) * this.max);
            const previous = Math.round((acc / 100) * this.max);

            if (current <= next && current > previous && current - next < current - previous) {
                acc = value;
            }

            return acc;
        }, 0);

    }
}
