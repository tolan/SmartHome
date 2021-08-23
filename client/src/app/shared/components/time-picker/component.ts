import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core';

interface Value {
    hours: number,
    minutes: number,
}

@Component({
    selector: 'time-picker',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class TimePickerComponent implements OnInit {

    @Input('value') public value: Value = {
        hours: null,
        minutes: null
    };
    @Output('onChange') onChange = new EventEmitter<Value>();

    public hours: number;
    public minutes: number;

    public hoursMax: number = 23;
    public minutesMax: number = 59;

    ngOnInit() {
        this.hours = this.value.hours;
        this.minutes = this.value.minutes;
    }

    hoursUp() {
        this.hours = this.hours + 1 > this.hoursMax ? 0 : this.hours + 1;
        this._emitValue();
    }

    hoursDown() {
        this.hours = this.hours - 1 < 0 ? this.hoursMax : this.hours - 1;
        this._emitValue();
    }

    hoursChange(value: string) {
        if (value !== '') {
            this.hours = Number(value);
            this._emitValue();
        }
    }

    minutesUp() {
        this.minutes = this.minutes + 1 > this.minutesMax ? 0 : this.minutes + 1;
        this._emitValue();
    }

    minutesDown() {
        this.minutes = this.minutes - 1 < 0 ? this.minutesMax : this.minutes - 1;
        this._emitValue();
    }

    minutesChange(value: string) {
        if (value !== '') {
            this.minutes = Number(value);
            this._emitValue();
        }
    }

    _emitValue() {
        this.onChange.emit({
            hours: this.hours,
            minutes: this.minutes,
        });
    }
}