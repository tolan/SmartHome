import {Component, OnInit, OnDestroy} from '@angular/core';

import {Task} from '../../../../interfaces/task'

import {TaskService} from '../../../../services/task'

@Component({
    selector: 'list',
    templateUrl: './list.html',
    styleUrls: ['./list.less']
})
export class ListComponent implements OnInit, OnDestroy {

    public tasks: Task[] = []

    constructor(private taskService: TaskService) {}

    ngOnInit() {
        this.taskService.getTasks().subscribe((tasks: Task[]) => {
            this.tasks = tasks.sort((a: Task, b: Task): number => {
                const nameA = a.task.name.toUpperCase()
                const nameB = b.task.name.toUpperCase()
                if (nameA < nameB) {
                    return -1;
                }
                if (nameA > nameB) {
                    return 1;
                }

                return 0;
            })
        }, 'SchedulerListComponent')
    }

    ngOnDestroy() {
        this.taskService.getTasks().unsubscribe('SchedulerListComponent')
    }
}