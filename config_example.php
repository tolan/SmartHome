<?php

return [
    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'SmartHome',
        'password' => 'password',
        'dbname' => 'SmartHome',
        'charset' => 'UTF8',
    ],
    'mqtt' => [
        'server' => 'localhost',
        'port' => 1883,
        'username' => 'admin',
        'password' => 'password',
        'client_id' => 'SmartHome-mqtt',
    ],
    'session' => [
        'name' => 'smartHome_session',
        'autorefresh' => true,
        'lifetime' => '1 day',
    ],
    'redis' => [
        'host' => 'localhost',
        'password' => 'password',
    ],
];
