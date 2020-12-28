<?php

use SmartHome\Rest\Permission\Controller;

$app->get('/api/0/permissions', Controller::class.':permissions');
$app->post('/api/0/permission', Controller::class.':update');