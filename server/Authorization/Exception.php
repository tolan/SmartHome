<?php

namespace SmartHome\Authorization;

use Slim\Exception\HttpException;
use SmartHome\Enum\HttpStatusCode;

/**
 * This file defines class for authozization exception
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Exception extends HttpException {

    /**
     * Status code
     *
     * @var integer
     */
    protected $code = HttpStatusCode::UNAUTHORIZED;

}
