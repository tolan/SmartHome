<?php

use SmartHome\Rest\Group\Controller;

$app->get('/api/0/groups', Controller::class.':groups');
$app->put('/api/0/group', Controller::class.':create');
$app->post('/api/0/group', Controller::class.':update');
$app->delete('/api/0/group/{id}', Controller::class.':delete');
