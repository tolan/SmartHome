export interface Firmware {
    firmware: {
        id: number;
        name: string;
        filename: string;
        tmpFilename?: string;
    },
    devices?: [];
}