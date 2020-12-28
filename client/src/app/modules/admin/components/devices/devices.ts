import {Component, OnInit} from '@angular/core';

import {DeviceService} from '../../../../services/device';

import {Device} from '../../../../interfaces/device';

@Component({
    selector: 'devices',
    templateUrl: './devices.html',
    styleUrls: ['./devices.less']
})
export class DevicesComponent implements OnInit {

    devices: Device[] = [];

    constructor(private deviceService: DeviceService) {}

    ngOnInit() {
        this.deviceService.getDevices().subscribe((devices: [Device]) => {
            this.devices = devices;
        }, 'DevicesComponent');
    }

    deviceRemove(device: Device) {
        this.deviceService.removeDevice(device);
    }

    deviceSave(device: Device) {
        this.deviceService.saveDevice(device);
    }

    deviceRestart(device: Device) {
        this.deviceService.restartDevice(device);
    }
}