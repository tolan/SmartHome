<?php

require_once __DIR__.'/bootstrap.php';

$container       = $app->getContainer();
$documentManager = $container->get('mongo');

return new \Symfony\Component\Console\Helper\HelperSet(
        ['dm' => new \Doctrine\ODM\MongoDB\Tools\Console\Helper\DocumentManagerHelper($dm)]
);
