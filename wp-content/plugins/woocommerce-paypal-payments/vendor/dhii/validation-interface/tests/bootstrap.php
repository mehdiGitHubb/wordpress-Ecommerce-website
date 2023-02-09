<?php

(function (string $basePath) {
    $baseDir = dirname($basePath, 2);
    define('ROOT_DIR', $baseDir);

    /** @noinspection PhpIncludeInspection */
    require_once "$baseDir/vendor/autoload.php";
})(__FILE__);
