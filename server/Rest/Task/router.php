<?php

use SmartHome\Rest\Task\Controller;

$app->get('/api/0/tasks', Controller::class.':tasks');
$app->put('/api/0/task', Controller::class.':create');
$app->post('/api/0/task', Controller::class.':update');
$app->delete('/api/0/task/{id}', Controller::class.':delete');

$app->get('/api/0/task/logs', Controller::class.':logs');
