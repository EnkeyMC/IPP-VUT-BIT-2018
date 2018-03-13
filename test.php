<?php

require_once 'autoload.php';

try {
    $tester = \TestSuite\TesterApp::getInstance();
    $tester->setRootDir(__DIR__);
    exit($tester->run());
} catch (Exception $e) {
    fwrite(STDERR, $e->getMessage().PHP_EOL);
    exit($e->getCode());
}
