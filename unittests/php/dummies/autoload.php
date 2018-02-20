<?php

include_once __DIR__.'/../../../autoload.php';

spl_autoload_register(function ($class_name) {
    @include $class_name . '.php';
});