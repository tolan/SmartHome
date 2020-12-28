<?php

namespace SmartHome\Enum;

use \SplEnum;

class Permission extends SplEnum {

    const TYPE_SECTION_DEVICES = 'section_devices';
    const TYPE_SECTION_SETTINGS = 'section_settings';
    const TYPE_SECTION_ADMIN = 'section_admin';
    const TYPE_DEVICE_CONTROL = 'device_control';

}
