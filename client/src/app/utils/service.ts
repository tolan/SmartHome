
let id: number = 1

export const getAll = function (...args: any[]): string[] {
    const next: (...data: any[]) => any = args.pop()
    const subsribers = args

    let results: any[] = []

    const callback = (index: number) => (...response: any[]) => {
        results[index] = response
        if (results.filter((item) => item).length === subsribers.length) {
            next(...results)
        }
    }

    return subsribers.map((subscriber: {subscribe: (...data: any[]) => any}, index: number) => {
        const key = 'subscribe-' + id++
        subscriber.subscribe(callback(index), key)
        return key
    })
}