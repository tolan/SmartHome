<?php

require_once __DIR__.'/bootstrap.php';

$container     = $app->getContainer();
$entityManager = $container->get('db');

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($entityManager);