<?php

use SmartHome\Html\Home\Controller;

/* @var $app \Slim\App */
$app->get('/', Controller::class.':home');