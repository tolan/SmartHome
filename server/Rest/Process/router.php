<?php

use SmartHome\Rest\Process\Controller;

$app->get('/api/0/processes', Controller::class.':processes');
$app->post('/api/0/process/restart', Controller::class.':restart');
