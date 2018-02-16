<?php

spl_autoload_register(function ($class_name) {
    if (strpos($class_name, 'Exception') !== false)
        include 'classes/php/Exceptions.php';
    else
        include 'classes/php/' . $class_name . '.php';
});