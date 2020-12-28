<?php

namespace SmartHome\Common;

use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SmartHome\Enum\HttpStatusCode;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Notification {

    const HEADER_NAME = 'X-Notification';

    public static function withResponse (Response $response, string $type) {
        return $response->withHeader(self::HEADER_NAME, $type)->withStatus(HttpStatusCode::BAD_REQUEST);
    }

    public static function cleanRequest(Request $request) {
        return $request->withoutHeader(self::HEADER_NAME);
    }
}
