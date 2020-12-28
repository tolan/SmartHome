import {Injectable} from '@angular/core';
import {HttpEvent, HttpErrorResponse, HttpInterceptor, HttpHandler, HttpRequest, HttpResponse} from '@angular/common/http';
import {Observable} from 'rxjs';
import {tap} from 'rxjs/operators';

import {ContainerFactory, Container} from '../lib/container';
import {NotificationType} from '../enums/notifications';

@Injectable()
export class NotificationInterceptor implements HttpInterceptor {

    private container: Container;

    constructor(containerFactory: ContainerFactory) {
        this.container = containerFactory.getContainer('Notification');
    }

    intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
        return next.handle(req).pipe(tap((event: HttpEvent<any>) => {
            if (event instanceof HttpResponse) {
                const nofitication = event.headers.get('X-Notification');
                if (nofitication) {
                    this.container.set('notification', {type: nofitication});
                }
            }
        }, (err: HttpErrorResponse) => {
            if (err instanceof HttpErrorResponse) {
                const status = err.status;
                const nofitication = err.headers.get('X-Notification');

                if (nofitication) {
                    this.container.set('notification', {type: nofitication});
                } else if (status === 401) {
                    this.container.set('notification', {type: NotificationType.UNAUTHORIZED});
                    window.location.href = window.location.href
                } else {
                    this.container.set('notification', {type: NotificationType.SERVER_ERROR});
                }
            }
        }));
    }
}