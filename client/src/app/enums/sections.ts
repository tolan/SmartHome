import {Permissions} from './permissions';

export interface SectionInterface {
    name: string,
    link: string,
};

export const Sections = {
    [`${Permissions.TYPE_SECTION_DEVICES}`]: {
        name: 'Zařízení',
        link: '/devices',
        class: '',
    },
    [`${Permissions.TYPE_SECTION_SCHEDULER}`]: {
        name: 'Plánovač',
        link: '/scheduler',
        class: 'no-mobile',
    },
    [`${Permissions.TYPE_SECTION_SETTINGS}`]: {
        name: 'Nastavení',
        link: '/settings',
        class: '',
    },
    [`${Permissions.TYPE_SECTION_ADMIN}`]: {
        name: 'Administrace',
        link: '/admin',
        class: '',
    },
};
