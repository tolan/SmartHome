import {Component, OnInit, OnDestroy} from '@angular/core';
import {ActivatedRoute} from '@angular/router';

import {DeviceService} from '../../../../services/device';

import {Device} from '../../../../interfaces/device';
import {Module} from '../../../../interfaces/module';
import {Control} from '../../../../interfaces/control';

@Component({
    selector: 'devices',
    templateUrl: './devices.html',
    styleUrls: ['./devices.less']
})
export class DevicesComponent implements OnInit, OnDestroy {

    private devices: Device[] = [];
    public roomId: string = null;
    public edited: {
        [key: number]: {
            [key: number]: {
                [key: number]: Control,
            },
        }
    } = {};
    public opened: {
        [key: number]: boolean,
    } = {};

    constructor(private route: ActivatedRoute, private deviceService: DeviceService) {}

    ngOnInit() {
        this.deviceService.getControlled().subscribe((devices: Device[]) => {
            this.devices = devices
            this.opened = this.devices.reduce((acc: {[key: number]: boolean}, {modules}) => {
                modules.forEach((module: Module) => {
                    if (!acc[module.module.id]) {
                        acc[module.module.id] = false;
                    }
                });

                return acc;
            }, this.opened);
        }, 'DevicesDevicesComponent');

        this.route.paramMap.subscribe(params => {
            this.roomId = params.get('id');
        });
    }

    ngOnDestroy() {
        this.deviceService.getControlled().unsubscribe('DevicesDevicesComponent');
    }

    getDevices() {
        return this.devices.filter((device: Device) => {
            return !this.roomId || device.room.id === Number(this.roomId);
        });
    }

    onModuleControl(device: Device, module: Module, control: Control) {
        const data = {
            device: {...device.device},
            module: {...module.module},
            control: {...control.control},
        }

        if (this.edited[device.device.id] && this.edited[device.device.id][module.module.id]) {
            delete(this.edited[device.device.id][module.module.id][control.control.id]);
        }

        this.deviceService.control(data);
    }

    onModuleEdit(device: Device, module: Module, control: Control) {
        if (!this.edited[device.device.id]) {
            this.edited[device.device.id] = {};
        }
        if (!this.edited[device.device.id][module.module.id]) {
            this.edited[device.device.id][module.module.id] = {};
        }

        this.edited[device.device.id][module.module.id][control.control.id] = control;
    }

    onModuleReset(device: Device, module: Module) {
        if (this.edited[device.device.id] && this.edited[device.device.id][module.module.id]) {
            const edited = Object.assign({}, this.edited);
            delete(this.edited[device.device.id][module.module.id]);
            this.edited = edited;
        }
    }

    onModuleToggle(module: Module) {
        this.opened[module.module.id] = !this.opened[module.module.id];
    }
}