import {HTTP_INTERCEPTORS} from '@angular/common/http';

import {LoaderInterceptor} from './interceptors/loader';
import {NotificationInterceptor} from './interceptors/notification';

export const interceptorProviders = [
    {provide: HTTP_INTERCEPTORS, useClass: LoaderInterceptor, multi: true},
    {provide: HTTP_INTERCEPTORS, useClass: NotificationInterceptor, multi: true},
];