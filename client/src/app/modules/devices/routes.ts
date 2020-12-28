import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';
import {NavigationEnd} from '@angular/router';

import {RootComponent} from './components/root/root';
import {DevicesComponent} from './components/devices/devices';

import {ContainerFactory} from '../../lib/container';
import {Device} from '../../interfaces/device';

const devicesRoutes: Routes = [
    {
        path: '',
        component: RootComponent,
        children: [
            {path: '', component: DevicesComponent},
            {
                path: 'room/:id',
                component: DevicesComponent,
                data: {
                    title: (event: NavigationEnd, containerFactory: ContainerFactory, next) => {
                        const container = containerFactory.getContainer('DeviceService').get('devices');
                        container.subscribe((devices: Device[]) => {
                            const match = event.url.match(/\d+/)
                            const roomId = match && Number(match[0]);
                            devices.some((device: Device) => {
                                if (device.room.id === roomId) {
                                    next(device.room.name);
                                    return true;
                                }

                                return false;
                            });

                            container.unsubscribe('RouterTitleDevice');
                        }, 'RouterTitleDevice')
                    },
                },
            },
        ],
    }
];

@NgModule({
    imports: [
        RouterModule.forChild(devicesRoutes),
    ],
    exports: [
        RouterModule
    ]
})
export class DevicesRoutingModule {}