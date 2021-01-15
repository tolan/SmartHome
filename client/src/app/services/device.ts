import {Injectable} from '@angular/core';
import {HttpClient, HttpHeaders} from '@angular/common/http';
import {ContainerFactory, Container} from '../lib/container';
import {Mediator, buildKeys, EventGroup, EventAction} from '../lib/mediator';

import {interval} from 'rxjs';
import {timeInterval, filter} from 'rxjs/operators';

import {Device} from '../interfaces/device';
import {Module} from '../interfaces/module';

interface Monitor {
    module: Module,
    cb: (module: Module) => void
};

@Injectable({
    providedIn: 'root'
})
export class DeviceService {

    private container: Container;
    private subject: any;
    private monitors: Monitor[] = [];

    constructor(private http: HttpClient, private mediator: Mediator, containerFactory: ContainerFactory) {
        this.container = containerFactory.getContainer('DeviceService');
        this._fetchDevices();

        this.subject = interval(2500);
        this.subject.pipe(
            timeInterval(),
            filter(() => Boolean(this.monitors.length))
        ).subscribe(() => {
            const modules = this.monitors.map((monitor: Monitor) => monitor.module.module);
            const headers = new HttpHeaders({'X-Silent': 'true'});
            this.http.post('/api/0/device/modules', {modules}, {headers}).subscribe((modules: Module[]) => {
                modules.forEach((module: Module) => {
                    const monitor: Monitor = this.monitors.find((monitor: Monitor) => monitor.module.module.id === module.module.id)
                    if (monitor) {
                        monitor.cb(module);
                    }
                });
            });
        });

        this.mediator.on(buildKeys(EventGroup.ROOM), () => this._fetchDevices());
        this.mediator.on(buildKeys(EventGroup.FIRMWARE), () => this._fetchDevices());
    }

    getDevices() {
        return this.container.get('devices');
    }

    saveDevice(device: Device) {
        this.http.put('/api/0/device', {...device}).subscribe(() => {
            this._fetchDevices();
            this.mediator.emit(buildKeys(EventGroup.DEVICE, EventAction.update), device);
        });
    }

    removeDevice(device: Device) {
        this.http.delete('/api/0/device/' + device.device.id).subscribe(() => {
            this._fetchDevices();
            this.mediator.emit(buildKeys(EventGroup.DEVICE, EventAction.remove), device);
        });
    }

    restartDevice(device: Device) {
        this.http.get('/api/0/device/' + device.device.id + '/restart').subscribe(() => {
            setTimeout(() => {
                this._fetchDevices();
                this.mediator.emit(buildKeys(EventGroup.DEVICE, EventAction.update), device);
            }, 5000);
        });
    }

    getControlled() {
        return this.container.get('controlled');
    }

    control(data: any) {
        this.http.post('/api/0/device/control', data).subscribe(() => {
            this._fetchDevices();
            this.mediator.emit(buildKeys(EventGroup.DEVICE, EventAction.update), null);
        });
    }

    registerModuleMonitor(module: Module, cb: (module: Module) => void) {
        this.monitors.push({module, cb});
        return this;
    }

    unRegisterModuleMonitor(module: Module) {
        this.monitors = this.monitors.filter((monitor: Monitor) => monitor.module.module.id !== module.module.id);
        return this;
    }

    private _fetchDevices() {
        this.http.get('/api/0/device/controlled').subscribe((devices: Device[]) => {
            this.container.set('controlled', devices);
        });

        this.http.get('/api/0/devices').subscribe((devices: [Device]) => {
            this.container.set('devices', devices, true);
        });
    }
}
