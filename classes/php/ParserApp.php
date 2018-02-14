<?php

class ParserApp extends App
{
    const OPTIONS = ['help' => 'h'];

    public function run() {
        if ($this->getConfig('help')) {
            $this->printHelp();
            return;
        }
    }

    protected function loadConfiguration() {
        $argParser = new ArgParser(self::OPTIONS);
        $this->configuration = $argParser->parseArguments();
    }

    private function printHelp() {
        echo 'Parse source code in programming language IPPcode18 from standard input and output it to standard output as XML for further interpretation' . PHP_EOL;
        echo PHP_EOL;
        echo 'USAGE:' . PHP_EOL;
        echo '    php parse.php [OPTION]' . PHP_EOL;
        echo 'OPTIONS:' . PHP_EOL;
        echo '    -h, --help    Print this help' . PHP_EOL;
    }
}