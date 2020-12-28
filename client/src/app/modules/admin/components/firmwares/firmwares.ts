import {Component, OnInit} from '@angular/core';

import {FirmwareService} from '../../../../services/firmware';

import {Firmware} from '../../../../interfaces/firmware';

@Component({
    selector: 'firmwares',
    templateUrl: './firmwares.html',
    styleUrls: ['./firmwares.less']
})
export class FirmwaresComponent implements OnInit {

    firmwares: Firmware[] = [];

    constructor(private firmwareService: FirmwareService) {}

    ngOnInit() {
        this.firmwareService.getFirmwares().subscribe((firmwares: [Firmware]) => {
            this.firmwares = firmwares;
        }, 'FirmwaresComponent');
    }

    firmwareRemove(firmware: Firmware) {
        if(!firmware.firmware.id) {
            this.firmwares = this.firmwares.filter((item) => item !== firmware);
        } else {
            this.firmwareService.removeFirmware(firmware);
        }
    }

    firmwareSave(firmware: Firmware) {
        this.firmwareService.saveFirmware(firmware);
    }

    uploadFile(firmware: Firmware, file: File) {
        this.firmwareService.uploadFile(file).subscribe((response: {filename: string}) => {
            firmware.firmware.tmpFilename = response.filename;
        });;
    }

    firmwareAdd() {
        this.firmwares.push({
            firmware: {
                id: null,
                name: null,
                filename: null,
            },
        });
    }
}