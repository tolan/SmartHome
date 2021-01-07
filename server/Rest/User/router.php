<?php

use SmartHome\Rest\User\Controller;

$app->get('/api/0/users', Controller::class.':users');

$app->get('/api/0/user', Controller::class.':get');
$app->put('/api/0/user', Controller::class.':create');
$app->post('/api/0/user', Controller::class.':update');
$app->delete('/api/0/user/{id}', Controller::class.':delete');
$app->post('/api/0/user/{id}/apiToken/generate', Controller::class.':generateApiToken');

$app->post('/api/0/user/self', Controller::class.':updateSelf');

$app->post('/api/0/user/login', Controller::class.':login');
$app->post('/api/0/user/logout', Controller::class.':logout');
