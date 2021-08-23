import {Component, OnInit, OnDestroy} from '@angular/core'
import {ActivatedRoute} from '@angular/router'
import {Router} from '@angular/router'
import clone from 'clone'

import {Task, Trigger, Validation, Condition, Action, Output as Out} from '../../../../interfaces/task'

import {TaskService} from '../../../../services/task'

import {TriggerMetaType} from '../../../../enums/triggerType'

import {mergeOutputs} from './output/utils'

@Component({
    selector: 'edit',
    templateUrl: './edit.html',
    styleUrls: ['./edit.less']
})
export class EditComponent implements OnInit, OnDestroy {

    public task: Task = null

    public TriggerMetaType = TriggerMetaType

    public validation: {
        name: boolean,
        triggers: boolean,
        conditions: boolean,
        actions: boolean,
    } = {
            name: false,
            triggers: false,
            conditions: true,
            actions: false,
        }

    constructor(private route: ActivatedRoute, private router: Router, private taskService: TaskService) {}

    ngOnInit() {
        this.route.paramMap.subscribe(params => {
            const id = params.get('id')
            if (id) {
                this.taskService.getTasks().subscribe((tasks: Task[]) => {
                    this.validation = {
                        name: false,
                        triggers: false,
                        conditions: true,
                        actions: false,
                    }
                    this.task = clone(tasks.find((task: Task) => task.task.id === id))
                    if (this.task) {
                        this.validation.name = !!this.task.task.name
                    }
                }, 'SchedulerEditComponent')
            } else {
                this.task = {
                    task: {
                        name: '',
                        enabled: true,
                        share: true,
                    },
                    triggers: [],
                    conditions: [],
                    actions: [],
                }
            }
        })
    }

    ngOnDestroy() {
        this.taskService.getTasks().unsubscribe('SchedulerEditComponent')
    }

    changeName(name: string) {
        this.validation.name = !!name
        this.task.task.name = name
    }

    changeShare() {
        this.task.task.share = !this.task.task.share
    }

    changeEnabled() {
        this.task.task.enabled = !this.task.task.enabled
    }

    changeTriggers(triggers: Trigger[]) {
        this.task.triggers = triggers
    }

    changeTriggersValidation(validation: Validation) {
        this.validation.triggers = validation.isValid
    }

    changeConditions(conditions: Condition[]) {
        this.task.conditions = conditions
    }

    changeConditionsValidation(validation: Validation) {
        this.validation.conditions = validation.isValid
    }

    changeActions(actions: Action[]) {
        this.task.actions = actions
    }

    changeActionsValidation(validation: Validation) {
        this.validation.actions = validation.isValid
    }

    onSave() {
        const listener = this.taskService.saveTask(this.task)
        listener.subscribe((task: Task) => {
            listener.unsubscribe('SchedulerEditComponent:saveTask')
            if (task && task.task.id) {
                this.router.navigate(['/scheduler/task', task.task.id])
            }
        }, 'SchedulerEditComponent:saveTask')
    }

    onDelete() {
        this.taskService.removeTask(this.task)
    }

    isValid() {
        return Object.values(this.validation).every((isValid) => isValid)
    }

    getOutput(): Out {
        return mergeOutputs(this.task.triggers)
    }
}