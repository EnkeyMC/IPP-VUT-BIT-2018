<?php

namespace TestSuite;


class TesterApp extends \App
{
    const OPTIONS = [
        'help' => 'h',
        'directory:' => 'd:',
        'recursive' => 'r',
        'parse-script:' => 'p:',
        'int-script:' => 'i:'
    ];

    public function run()
    {
        if ($this->getConfig('help')) {
            $this->printHelp();
            return \ExitCodes::SUCCESS;
        }

        var_dump(\OSUtils::getFilesInDirByRegex('tests/', '/.+\.src$/i', true));
    }

    protected function loadConfiguration()
    {
        $argParser = new \ArgParser(self::OPTIONS);
        $this->configuration = $argParser->parseArguments();
        $this->checkArguments();
        $this->fillDefaultConfiguration();
    }

    private function checkArguments() {
        // TODO check arguments
    }

    private function fillDefaultConfiguration() {
        if ($this->getConfig('directory') === false)
            $this->configuration['directory'] = '.';
        if ($this->getConfig('parse-script') === false)
            $this->configuration['parse-script'] = 'parse.php';
        if ($this->getConfig('int-script') === false)
            $this->configuration['int-script'] = 'interpret.py';
    }

    private function printHelp() {
        echo 'Testovaci ramec pro testovani prekladace a interpretu jazyka IPPcode18.' . PHP_EOL;
        echo PHP_EOL;
        echo 'POUZITI:' . PHP_EOL;
        echo '    php test.php [MOZNOSTI]' . PHP_EOL;
        echo 'MOZNOSTI:' . PHP_EOL;
        echo '    -h, --help                   Vypise tuto napovedu' . PHP_EOL;
        echo '    -d, --directory <adresar>    Testy bude hledat v zadanem adresari (chybi-li tento parametr, tak skript prochazi aktualni adresar)' . PHP_EOL;
        echo '    -r, --recursive              Testy bude hledat i v podadresarich' . PHP_EOL;
        echo '    -p  --parse-script <soubor>  Soubor se skriptem v PHP 5.6 pro analyzu zdrojoveho kodu (vychozi parse.php v aktualnim adresari)' . PHP_EOL;
        echo '    -i, --int-script <soubor>    Soubor se skriptem v Python 3.6 pro interpretaci XML reprezentace kodu IPPcode18 (vychozi interpret.py v aktualnim adresari)' . PHP_EOL;
    }
}