<?php

use SmartHome\Rest\Room\Controller;

$app->get('/api/0/rooms', Controller::class.':rooms');
$app->put('/api/0/room', Controller::class.':create');
$app->post('/api/0/room', Controller::class.':update');
$app->delete('/api/0/room/{id}', Controller::class.':delete');
