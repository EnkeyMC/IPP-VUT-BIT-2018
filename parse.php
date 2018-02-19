<?php

require_once 'autoload.php';

$parser = ParserApp::getInstance();
exit($parser->run());
