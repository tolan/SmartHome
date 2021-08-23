<?php

$sources = array_reduce(['Html', 'Rest'], function($acc, $path) {
    return array_merge($acc, glob(__DIR__.'/server/'.$path.'/*', GLOB_ONLYDIR));
}, []);

foreach ($sources as $dir) {
    foreach (['factory.php', 'router.php'] as $file) {
        $filename = $dir.'/'.$file;
        if (file_exists($filename)) {
            require_once $filename;
        }
    }
}
