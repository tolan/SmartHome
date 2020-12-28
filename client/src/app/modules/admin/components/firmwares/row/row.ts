import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core';
import {FormBuilder} from '@angular/forms';

import {Firmware} from '../../../../../interfaces/firmware';

@Component({
    selector: 'firmwares-row',
    templateUrl: './row.html',
    styleUrls: ['./row.less']
})
export class FirmwaresRowComponent implements OnInit {

    @Input('firmware') public firmware: Firmware;
    @Output('onSave') onSave = new EventEmitter<Firmware>();
    @Output('onRemove') onRemove = new EventEmitter<Firmware>();
    @Output('onFileUpload') onFileUpload = new EventEmitter<File>();

    public firmwareForm: any;
    public uploadForm: any;

    public isEditing: boolean = false;
    public isRemoving: boolean = false;

    constructor(private formBuilder: FormBuilder) {}

    ngOnInit() {
        if (!this.firmware.firmware.id) {
            this.edit();
        }

        this.firmwareForm = this.formBuilder.group({
            name: this.firmware.firmware.name,
        });
        this.uploadForm = this.formBuilder.group({
            file: null,
        });
    }

    fileUpload(fileInput: any) {
        if (fileInput.target.files && fileInput.target.files[0]) {
            const file = fileInput.target.files[0]
            this.onFileUpload.emit(file);
        }
    }

    edit() {
        this.isRemoving = false;
        this.isEditing = true;
    }

    remove() {
        this.isRemoving = true;
        this.isEditing = false;
    }

    save() {
        if (this.isRemoving) {
            this.onRemove.emit(this.firmware);
        } else {
            const firmware = {
                firmware: {
                    ...this.firmware.firmware,
                    ...this.firmwareForm.value,
                    filename: this.firmware.firmware.tmpFilename,
                }
            }

            this.onSave.emit(firmware);
        }
    }

    cancel() {
        if (!this.firmware.firmware.id) {
            this.onRemove.emit(this.firmware);
        } else {
            this.isEditing = false;
            this.isRemoving = false;
        }
    }

}