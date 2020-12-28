<?php

use SmartHome\Rest\Device\Controller;

$app->get('/api/0/devices', Controller::class.':list');
$app->put('/api/0/device', Controller::class.':update');
$app->delete('/api/0/device/{id}', Controller::class.':delete');
$app->get('/api/0/device/{id}/restart', Controller::class.':restart');

$app->post('/api/0/device/register', Controller::class.':register');

$app->get('/api/0/device/controlled', Controller::class.':controlled');
$app->post('/api/0/device/modules', Controller::class.':modules');
$app->post('/api/0/device/control', Controller::class.':control');

$app->get('/api/0/{apiToken}/module/{id}/{action}/{value}', Controller::class.':remoteControl');
