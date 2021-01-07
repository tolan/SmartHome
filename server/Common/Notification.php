<?php

namespace SmartHome\Common;

use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SmartHome\Enum\HttpStatusCode;

/**
 * This file defines class for handling notification message to frontend client.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Notification {

    const HEADER_NAME = 'X-Notification';

    /**
     * Assigns message type to the response
     *
     * @param Response $response Reponse
     * @param string   $type     Notification type
     *
     * @return Response
     */
    public static function withResponse(Response $response, string $type) {
        return $response->withHeader(self::HEADER_NAME, $type)->withStatus(HttpStatusCode::BAD_REQUEST);
    }

    /**
     * Cleans notigication in request
     *
     * @param Request $request Request
     *
     * @return Request
     */
    public static function cleanRequest(Request $request) {
        return $request->withoutHeader(self::HEADER_NAME);
    }

}
