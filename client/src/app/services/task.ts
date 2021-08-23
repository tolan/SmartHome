import {Injectable} from '@angular/core'
import {HttpClient, HttpParams} from '@angular/common/http'
import {ContainerFactory, Container, Listeners} from '../lib/container'
import {Mediator, buildKeys, EventGroup, EventAction} from '../lib/mediator'

import {Task, Log} from '../interfaces/task'

@Injectable({
    providedIn: 'root'
})
export class TaskService {

    private container: Container

    constructor(private http: HttpClient, private mediator: Mediator, containerFactory: ContainerFactory) {
        this.container = containerFactory.getContainer('TaskService')
        this._fetchTasks()
        this.mediator.on(buildKeys(EventGroup.DEVICE), () => this._fetchTasks())
        this.mediator.on(buildKeys(EventGroup.ROOM), () => this._fetchTasks())
    }

    getTasks() {
        return this.container.get('tasks')
    }

    getLogs() {
        return this.container.get('logs')
    }

    fetchLogs(taskId: string, limit: number, page: number) {
        this.container.unset('logs')
        const params = new HttpParams()
            .set('taskId', taskId)
            .set('limit', limit.toString())
            .set('skip', (limit * page).toString())

        this.http.get('/api/0/task/logs', {params}).subscribe((result: {logs: Log[], count: number}) => {
            this.container.set('logs', result)
        })

        return this
    }

    saveTask(task: Task): Listeners {
        this.container.unset('saved-task')
        let request
        if (!task.task.id) {
            request = this.http.put('/api/0/task', {...task})
            request.subscribe((saved: Task) => {
                this._fetchTasks()
                this.mediator.emit(buildKeys(EventGroup.TASK, EventAction.create), task)
                this.container.set('saved-task', saved)
            })
        } else {
            request = this.http.post('/api/0/task', {...task})
            request.subscribe((saved: Task) => {
                this._fetchTasks()
                this.mediator.emit(buildKeys(EventGroup.TASK, EventAction.update), task)
                this.container.set('saved-task', saved)
            })
        }

        return this.container.get('saved-task')
    }

    removeTask(task: Task) {
        this.http.delete('/api/0/task/' + task.task.id).subscribe(() => {
            this._fetchTasks()
            this.mediator.emit(buildKeys(EventGroup.TASK, EventAction.remove), task)
        })
    }

    private _fetchTasks() {
        this.http.get('/api/0/tasks').subscribe((tasks: Task[]) => {
            this.container.set('tasks', tasks)
        })
    }
}
