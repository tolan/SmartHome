<?php

use SmartHome\Rest\Firmware\Controller;

$app->get('/api/0/firmwares', Controller::class.':firmwares');
$app->put('/api/0/firmware', Controller::class.':create');
$app->post('/api/0/firmware', Controller::class.':update');
$app->delete('/api/0/firmware/{id}', Controller::class.':delete');
$app->post('/api/0/firmware/upload', Controller::class.':upload');
