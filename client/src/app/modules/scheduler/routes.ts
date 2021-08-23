import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';
import {NavigationEnd} from '@angular/router';

import {RootComponent} from './components/root/root';
import {EditComponent} from './components/edit/edit';

import {ContainerFactory} from '../../lib/container';

const schedulerRoutes: Routes = [
    {
        path: '',
        component: RootComponent,
        children: [
            {
                path: 'task/new',
                component: EditComponent,
                data: {
                    task: {},
                    title: 'Nový plán',
                },
            },
            {
                path: 'task/:id',
                component: EditComponent,
                data: {
                    title: (event: NavigationEnd, containerFactory: ContainerFactory, next) => {
                        //TODO
                        return 'TODO'
                    },
                },
            },
        ]
    }
];

@NgModule({
    imports: [
        RouterModule.forChild(schedulerRoutes)
    ],
    exports: [
        RouterModule
    ]
})
export class SchedulerRoutingModule {}