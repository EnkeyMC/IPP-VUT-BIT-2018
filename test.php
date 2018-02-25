<?php

require_once 'autoload.php';

try {
    $tester = \TestSuite\TesterApp::getInstance();
    exit($tester->run());
} catch (Exception $e) {
    fwrite(STDERR, $e->getMessage());
    exit($e->getCode());
}
