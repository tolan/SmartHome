export interface Module {
    module: {
        id: number;
        name: string;
        settingsData: any;
    },
    device?: any;
    controls?: any[];
}