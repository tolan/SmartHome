import {Component, OnInit} from '@angular/core';
import {MatSnackBar} from '@angular/material/snack-bar';

import {ContainerFactory, Container} from '../../lib/container';

import {Notification} from '../../interfaces/notification';

import {NotificationText} from '../../enums/notifications';

@Component({
    selector: 'notification',
    templateUrl: './notification.html',
    styleUrls: ['./notification.less']
})
export class NotificationComponent implements OnInit {

    private duration = 5000;

    private container: Container;

    constructor(private containerFactory: ContainerFactory, private _snackBar: MatSnackBar) {
        this.container = this.containerFactory.getContainer('Notification');
    }

    ngOnInit() {
        this.container.get('notification').subscribe((notification: Notification) => {
            const message = NotificationText[notification.type] || 'Error!!!';
            const action = 'X';
            this._snackBar.open(message, action, {
                duration: this.duration,
            });
        });
    }
}