<?php

namespace SmartHome\Authorization;

use Slim\Exception\HttpException;
use SmartHome\Enum\HttpStatusCode;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Exception extends HttpException
{
    protected $code = HttpStatusCode::UNAUTHORIZED;
}
