import {NgModule} from '@angular/core';
import {BrowserModule, Title} from '@angular/platform-browser';
import {RouterModule} from '@angular/router';
import {HttpClientModule} from '@angular/common/http';
import {BrowserAnimationsModule} from '@angular/platform-browser/animations';

import {ReactiveFormsModule} from '@angular/forms';
import {MatFormFieldModule} from '@angular/material/form-field';
import {MatInputModule} from '@angular/material/input';
import {MatButtonModule} from '@angular/material/button';
import {MatDividerModule} from '@angular/material/divider';
import {MatToolbarModule} from '@angular/material/toolbar';
import {MatProgressBarModule} from '@angular/material/progress-bar';
import {MatSnackBarModule} from '@angular/material/snack-bar';

import {ContainerFactory} from './lib/container';
import {Mediator} from './lib/mediator';

import {UserService} from './services/user';
import {UsersService} from './services/users';
import {DeviceService} from './services/device';
import {GroupService} from './services/group';

import {AppComponent} from './app.component';
import {NavigationComponent} from './components/navigation/navigation';
import {LoginComponent} from './components/login/login';
import {NotificationComponent} from './components/notification/notification';

import {AppRoutingModule} from './app.routing';

import {interceptorProviders} from './interceptors';

@NgModule({
    imports: [
        BrowserModule,
        HttpClientModule,
        RouterModule,

        BrowserAnimationsModule,

        ReactiveFormsModule,
        MatFormFieldModule,
        MatInputModule,
        MatButtonModule,
        MatDividerModule,
        MatToolbarModule,
        MatProgressBarModule,
        MatSnackBarModule,

        AppRoutingModule,
    ],
    declarations: [
        AppComponent,
        NavigationComponent,
        LoginComponent,
        NotificationComponent,
    ],
    providers: [
        Title,
        interceptorProviders,
        ContainerFactory,
        Mediator,
        UserService,
        UsersService,
        DeviceService,
        GroupService,
    ],
    bootstrap: [AppComponent]
})
export class AppModule {}
