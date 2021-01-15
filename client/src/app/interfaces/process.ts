export interface Process {
    process: {
        id: string;
        command: string;
        params: [];
        startTime: number;
        runningTime: number;
        starts: number;
        state: string;
    },
}