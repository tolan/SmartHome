export interface User {
    user: {
        id?: number;
        username: string;
        token?: string;
        apiToken?: string;
    },
    groups: [];
    permissions: [];
}