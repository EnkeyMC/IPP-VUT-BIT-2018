<?php

require_once 'autoload.php';

try {
    $parser = ParserApp::getInstance();
    exit($parser->run());
} catch (Exception $e) {
    fwrite(STDERR, $e->getMessage().PHP_EOL);
    exit($e->getCode());
}
