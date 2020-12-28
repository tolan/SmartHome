export interface Device {
    device: {
        id: number;
        name: string;
        mac: string;
        ipAddress: string;
        lastRegistration: string;
    },
    firmware?: any;
    room?: any;
    modules?: [];
}