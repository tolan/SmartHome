import {Component, EventEmitter, Input, Output, OnInit, OnDestroy} from '@angular/core';

import {UserControlType} from '../../../../../enums/controlType';

import {DeviceService} from '../../../../../services/device';

import {Device} from '../../../../../interfaces/device';
import {Module} from '../../../../../interfaces/module';
import {Control} from '../../../../../interfaces/control';

@Component({
    selector: 'module',
    templateUrl: './module.html',
    styleUrls: ['./module.less']
})
export class ModuleComponent implements OnInit, OnDestroy {

    @Input('roomId') public roomId: string;
    @Input('device') public device: Device;
    @Input('module') public module: Module;
    @Input('opened') public opened: boolean = false;
    @Input('edited') public edited: {[key: number]: Control};
    @Output('onControl') onControl = new EventEmitter<Control>();
    @Output('onEdit') onEdit = new EventEmitter<Control>();
    @Output('onReset') onReset = new EventEmitter();
    @Output('onToggle') onToggle = new EventEmitter();

    public controls: Control[];

    public ControlType = UserControlType;

    constructor(private deviceService: DeviceService) {}

    ngOnInit() {
        const controls = this._buildControls(this.module);
        this.controls = this._mergeControls(this.module, controls);

        this.deviceService.registerModuleMonitor(this.module, (module: Module) => this.moduleMonitor(module));
    }

    ngOnDestroy() {
        this.deviceService.unRegisterModuleMonitor(this.module);
    }

    onControlChange(control: Control) {
        this.onControl.emit(control);
    }

    onControlEdit(control: Control) {
        this.onEdit.emit(control);
    }

    reset() {
        this.controls = this._buildControls(this.module);
        this.onReset.emit();
    }

    moduleMonitor(module: Module) {
        this.controls = this._mergeControls(module, this.controls);
    }

    toggle() {
        this.onToggle.emit();
    }

    private _buildControls(module: Module) {
        return Object.values(this.ControlType).reduce((acc: Control[], type: string) => {
            return acc.concat(module.controls.find((control: Control) => {
                return type === control.control.type && control.control.controlData.active;
            }));
        }, []).filter(Boolean);
    }

    private _mergeControls(module: Module, controls: Control[]) {
        return this._buildControls(module).reduce((acc: Control[], control: Control, key: number) => {
            let originControl = acc.find((originControl: Control) => control.control.id === originControl.control.id);
            if (!originControl) {
                acc = acc.concat(control);
            } else if (!this.edited || !this.edited[control.control.id]) {
                acc[key] = Object.assign({}, originControl, control);
            }

            return acc
        }, controls)
    }
}