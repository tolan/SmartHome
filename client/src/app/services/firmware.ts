import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {ContainerFactory, Container} from '../lib/container';
import {Mediator, buildKeys, EventGroup, EventAction} from '../lib/mediator';

import {Firmware} from '../interfaces/firmware';

@Injectable({
    providedIn: 'root'
})
export class FirmwareService {

    private container: Container;

    constructor(private http: HttpClient, private mediator: Mediator, containerFactory: ContainerFactory) {
        this.container = containerFactory.getContainer('FirmwareService');
        this._fetchFirmwares();
    }

    getFirmwares() {
        return this.container.get('firmwares');
    }

    saveFirmware(firmware: Firmware) {
        if (!firmware.firmware.id) {
            this.http.put('/api/0/firmware', {...firmware}).subscribe(() => {
                this._fetchFirmwares();
                this.mediator.emit(buildKeys(EventGroup.FIRMWARE, EventAction.create), firmware);
            });
        } else {
            this.http.post('/api/0/firmware', {...firmware}).subscribe(() => {
                this._fetchFirmwares();
                this.mediator.emit(buildKeys(EventGroup.FIRMWARE, EventAction.update), firmware);
            });
        }
    }

    removeFirmware(firmware: Firmware) {
        this.http.delete('/api/0/firmware/' + firmware.firmware.id).subscribe(() => {
            this._fetchFirmwares();
            this.mediator.emit(buildKeys(EventGroup.FIRMWARE, EventAction.remove), firmware);
        });
    }

    uploadFile(file: File) {
        const formData: FormData = new FormData();
        formData.append('file', file, file.name);
        return this.http.post('/api/0/firmware/upload', formData);
    }

    private _fetchFirmwares() {
        this.http.get('/api/0/firmwares').subscribe((firmwares: [Firmware]) => {
            this.container.set('firmwares', firmwares);
        });
    }
}
