<?php

require_once 'autoload.php';

try {
    $parser = ParserApp::getInstance();
    $parser->setRootDir(__DIR__);
    exit($parser->run());
} catch (Exception $e) {
    fwrite(STDERR, $e->getMessage().PHP_EOL);
    exit($e->getCode());
}
