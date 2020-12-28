export enum NotificationType {
    SERVER_ERROR = 'serverError',
    INVALID_REQUEST_DATA = 'invalidRequestData',
    INVALID_OLD_PASSWORD = 'invalidOldPassword',
    NEW_PASSWORD_DONT_MATCH = 'newPasswordDontMatch',
    TOO_SHORT_PASSWORD = 'tooShortPassword',
    WRONG_PASSWORD = 'wrongPassword',
    UNAUTHORIZED = 'unauthorized',
}

export const NotificationText = {
    [`${NotificationType.SERVER_ERROR}`]: 'Chyba serveru!',
    [`${NotificationType.INVALID_REQUEST_DATA}`]: 'Nevalidní vstupní data!',
    [`${NotificationType.INVALID_OLD_PASSWORD}`]: 'Nesprávné původní heslo!',
    [`${NotificationType.NEW_PASSWORD_DONT_MATCH}`]: 'Nová hesla je neshodují!',
    [`${NotificationType.TOO_SHORT_PASSWORD}`]: 'Nové heslo je příliš krátké!',
    [`${NotificationType.WRONG_PASSWORD}`]: 'Nesprávné heslo!',
    [`${NotificationType.UNAUTHORIZED}`]: 'Uživatel není přihlášen.',
}