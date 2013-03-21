<?php

$try = array(
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php',
);

foreach($try as $path) {
    if (file_exists($path)) {
        include $path;
        break;
    }
}
