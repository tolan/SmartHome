<?php

namespace SmartHome\Enum\Scheduler\Action\Http;

use SplEnum;

/**
 * This file defines class for enum of HTTP methods.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Method extends SplEnum {

    const GET    = 'GET';
    const POST   = 'POST';
    const PUT    = 'PUT';
    const DELETE = 'DELETE';

}
