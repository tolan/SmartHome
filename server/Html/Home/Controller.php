<?php

namespace SmartHome\Html\Home;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Psr7\Stream;

/**
 * This file defines class for home controller
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Controller {

    /**
     * Home page
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function home(Request $request, Response $response) {
        $dir = __DIR__.'/../../../public/';
        if (file_exists($dir.'index.html.gz')) {
            $content  = fopen($dir.'index.html.gz', 'r');
            $response = $response->withHeader('Content-Encoding', 'gzip');
        } else {
            $content = fopen($dir.'index.html', 'r');
        }

        return $response->withBody(new Stream($content));
    }

}
