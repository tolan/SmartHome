import {Component, Input, OnChanges, OnDestroy} from '@angular/core'
import {PageEvent} from '@angular/material/paginator';
import {MatPaginatorIntl} from '@angular/material/paginator'

import {Log, Task} from '../../../../../interfaces/task'

import {TaskService} from '../../../../../services/task'

@Component({
    selector: 'logs',
    templateUrl: './template.html',
    styleUrls: ['./style.less']
})
export class LogsComponent implements OnChanges, OnDestroy {

    @Input('task') public task: Task

    public page: number = 0
    public limit: number = 20

    public displayedColumns: string[] = ['created', 'message'];
    public logs: Log[] = []
    public count: number = 0


    constructor(private taskService: TaskService, private paginator: MatPaginatorIntl) {
        this.paginator.itemsPerPageLabel = 'Počet záznamů na stránku:';
        this.paginator.getRangeLabel = (page: number, pageSize: number, length: number): string => {
            return Math.min(((page * pageSize) + 1), length) + ' - ' + Math.min(((page + 1) * pageSize), length) + ' z ' + length
        }
    }

    ngOnChanges() {
        this._fetch()
    }

    ngOnDestroy() {
        this.taskService.getLogs().unsubscribe('LogsComponent')
    }

    onChangePage(event: PageEvent) {
        this.page = event.pageIndex;
        this.logs = [];
        this._fetch();
    }

    private _fetch() {
        this.taskService
            .fetchLogs(this.task.task.id, this.limit, this.page)
            .getLogs().subscribe(({logs, count}: {logs: Log[], count: number}) => {
                this.logs = logs
                this.count = count
            }, 'LogsComponent')
    }
}
