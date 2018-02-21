<?php

require_once 'autoload.php';

try {
    $parser = ParserApp::getInstance();
} catch (InvalidArgumentException $e) {
    fwrite(STDERR, $e->getMessage());
    exit(ExitCodes::ERROR_PARAMETER);
}
exit($parser->run());
