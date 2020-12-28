<?php

namespace SmartHome\Enum;

use \SplEnum;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Notification extends SplEnum {

    const INVALID_REQUEST_DATA = 'invalidRequestData';
    const INVALID_OLD_PASSWORD = 'invalidOldPassword';
    const NEW_PASSWORD_DONT_MATCH = 'newPasswordDontMatch';
    const TOO_SHORT_PASSWORD = 'tooShortPassword';
    const WRONG_PASSWORD = 'wrongPassword';

}
