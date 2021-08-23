export interface Module {
    module: {
        id: number;
        name: string;
        type: string;
        settingsData: any;
    },
    device?: any;
    controls?: any[];
}