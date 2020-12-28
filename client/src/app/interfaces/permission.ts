export interface Permission {
    permission: {
        id: number;
        name: string;
        type: string;
    },
    groups?: [];
}