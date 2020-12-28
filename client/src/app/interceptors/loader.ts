import {Injectable} from '@angular/core';
import {HttpEvent, HttpInterceptor, HttpHandler, HttpRequest, HttpResponse} from '@angular/common/http';
import {Observable} from 'rxjs';
import {tap} from 'rxjs/operators';

import {ContainerFactory, Container} from '../lib/container';

@Injectable()
export class LoaderInterceptor implements HttpInterceptor {

    private container: Container;
    private requests = 0;

    constructor(containerFactory: ContainerFactory) {
        this.container = containerFactory.getContainer('Loader');
    }

    intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
        if (!req.headers.get('X-Silent')) {
            this.requests++;
            this.container.set('loading', this.requests);
        }

        return next.handle(req).pipe(tap((event: HttpEvent<any>) => {
            if (event instanceof HttpResponse && !req.headers.get('X-Silent')) {
                this.requests--;
                this.container.set('loading', this.requests);
            }
        }, () => {
            if (!req.headers.get('X-Silent')) {
                this.requests--;
                this.container.set('loading', this.requests);
            }
        }));
    }
}