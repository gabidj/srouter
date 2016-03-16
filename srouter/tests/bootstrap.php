<?php

require_once __DIR__ . '/../src/SRouter.php';

spl_autoload_register(function($class) {
    if (strpos($class, 'SRouter\\') === 0) {
        $dir = strcasecmp(substr($class, -4), 'Test') ? 'src/' : 'test/';
        $name = substr($class, strlen('SRouter'));
        require __DIR__ . '/../' . $dir . strtr($name, '\\', DIRECTORY_SEPARATOR) . '.php';
    }
});
