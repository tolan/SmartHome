<?php

namespace SmartHome\Enum;

use SplEnum;

/**
 * This file defines class for socket event type.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class SocketEventType extends SplEnum {

    const KEEP_ALIVE     = 'keepAlive';
    const MESSAGE        = 'smartHome/event/message';
    const PROCESS_STATES = 'smarthome/event/processStates';
    const REQUEST        = 'smartHome/event/request';
    const SUBSCRIBE      = 'smartHome/event/subscribe';
    const UNSUBSCRIBE    = 'smartHome/event/unsubscribe';

}
