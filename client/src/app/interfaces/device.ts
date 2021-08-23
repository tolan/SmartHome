export interface Device {
    device: {
        id: number;
        name: string;
        mac: string;
        ipAddress: string;
        lastRegistration: string;
        isActive: boolean;
    },
    firmware?: any;
    room?: any;
    modules?: [];
}