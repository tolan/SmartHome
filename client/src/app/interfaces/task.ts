
export interface Task {
    task: {
        id?: string,
        name: string,
        enabled: boolean,
        share: boolean,
    },
    triggers: Trigger[],
    conditions: Condition[],
    actions: Action[],
}

export interface Log {
    log: {
        id: string,
        created: string,
        message: string,
    },
}

export interface Trigger {
    trigger: {
        id?: number,
        type: string,
        data: Data,
    },
    conditions: Condition[],
    output: Output,
    meta: Meta,
}

export interface Action {
    id?: number,
    type: string,
    data: Data,
}

export interface Condition {
    id?: number,
    type: string,
    value: any
}

export interface Output {
    defaults: Data[],
    custom: Data[],
}

export interface Data {
    [key: string]: any,
}

export interface Validation {
    isValid: boolean,
    error?: string,
}

export interface Meta {
    valueType?: string,
}