<?php

require_once 'autoload.php';

try {
    $tester = \TestSuite\TesterApp::getInstance();
} catch (InvalidArgumentException $e) {
    fwrite(STDERR, $e->getMessage());
    exit(ExitCodes::ERROR_PARAMETER);
}
exit($tester->run());