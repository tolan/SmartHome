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
    'mongo'       => [
        'host'     => 'localhost',
        'port'     => 27017,
        'user'     => 'SmartHome',
        'password' => 'password',
        'dbname'   => 'SmartHome',
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
    'socket'      => [
        'allowed_origins' => [
            'localhost',
        ],
        'requests_per_minute' => 10,
        'connections_per_ip'  => 2,
    ],
    'elk'         => [
        'host' => 'localhost',
        'port' => 9200,
    ],
    'coordinates' => [
        'latitude'  => 0.0,
        'longitude' => 0.0,
    ]
];
