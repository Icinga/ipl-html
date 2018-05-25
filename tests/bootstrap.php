<?php

call_user_func(function () {
    if (! class_exists('PHPUnit_Framework_TestCase')) {
        require_once __DIR__ . '/phpunit-compat.php';
    }

    require_once dirname(__DIR__) . '/vendor/autoload.php';
    require_once __DIR__ . '/php/TestCase.php';
});
