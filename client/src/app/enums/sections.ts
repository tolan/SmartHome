import {Permissions} from './permissions';

export interface SectionInterface {
    name: string,
    link: string,
};

export const Sections = {
    [`${Permissions.TYPE_SECTION_DEVICES}`]: {
        name: 'Zařízení',
        link: '/devices',
    },
    [`${Permissions.TYPE_SECTION_SETTINGS}`]: {
        name: 'Nastavení',
        link: '/settings',
    },
    [`${Permissions.TYPE_SECTION_ADMIN}`]: {
        name: 'Administrace',
        link: '/admin',
    },
};
