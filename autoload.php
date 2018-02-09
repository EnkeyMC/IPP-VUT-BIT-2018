<?php
spl_autoload_register(function ($class_name) {
   include 'classes/php/' . $class_name . '.php';
});