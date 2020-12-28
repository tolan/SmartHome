<?php

namespace SmartHome\Html\Home;

//use DI\Container;
use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Controller {
//
//    protected $view;
//
//    public function __construct (Container $container) {
//        $this->view = $container->get('view');
//    }

    public function home (Request $request, Response $response) {
        $dir = __DIR__.'/../../../public/';
        if (file_exists($dir.'index.html.gz')) {
            $content = fopen($dir.'index.html.gz', 'r');
            $response = $response->withHeader('Content-Encoding', 'gzip');
        } else {
            $content = fopen($dir.'index.html', 'r');
        }

        return $response->withBody(new \Slim\Psr7\Stream($content));
//        return $this->view->render($response, 'index.html');
    }

}
