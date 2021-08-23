
import {Trigger, Output, Data} from '../../../../../interfaces/task';

export const mergeOutputs = (triggers: Trigger[]): Output => {
    const [defaults, custom] = ['defaults', 'custom'].map((type: string) => {
        return Object.values(triggers.reduce((acc: Data, trigger: Trigger) => {
            trigger.output[type].forEach((item: {key: string}) => {
                acc[item.key] = item
            })

            return acc
        }, {}))
    })

    return {defaults, custom}
}