<?php

namespace TestSuite;

/**
 * Class TesterApp
 * @package TestSuite
 *
 * Singleton
 *
 * Searches for test files in given directory and tests parse and interpret script with them
 */
class TesterApp extends \App
{
    /** Allowed options and aliases */
    const OPTIONS = [
        'help' => 'h',
        'directory:' => 'd:',
        'recursive' => 'r',
        'parse-script:' => 'p:',
        'int-script:' => 'i:',
        'php-int:' => '',
        'py-int:' => '',
        'temp-dir:' => 't:',
        'text' => ''
    ];

    /**
     * Run application
     *
     * @return int exit code
     */
    public function run()
    {
        if ($this->getConfig('help')) {
            $this->printHelp();
            return \ExitCodes::SUCCESS;
        }

        if ($this->getConfig('text'))
            $output = new TextTestOutput();
        else
            $output = new HTMLTestOutput();

        $sources = \OSUtils::getFilesInDirByRegex($this->getConfig('directory'), '/.+\.src$/i', $this->getConfig('recursive'));

        foreach ($sources as $source) {
            $testCase = new TestCase($source[0]);
            $result = $testCase->run();
            $output->addTestResult($result);
        }

        $output->renderOutput();
        return \ExitCodes::SUCCESS;
    }

    /**
     * Load application configuration
     */
    protected function loadConfiguration()
    {
        $argParser = new \ArgParser(self::OPTIONS);
        $this->configuration = $argParser->parseArguments();
        $this->checkArguments();
        $this->fillDefaultConfiguration();
    }

    /**
     * Check argument combinations
     */
    private function checkArguments() {
        // TODO check arguments
    }

    /**
     * Fill unspecified options with default values
     */
    private function fillDefaultConfiguration() {
        if ($this->getConfig('directory') === false)
            $this->configuration['directory'] = '.';
        if ($this->getConfig('parse-script') === false)
            $this->configuration['parse-script'] = 'parse.php';
        if ($this->getConfig('int-script') === false)
            $this->configuration['int-script'] = 'interpret.py';
        if ($this->getConfig('php-int') === false)
            $this->configuration['php-int'] = 'php5.6';
        if ($this->getConfig('py-int') === false)
            $this->configuration['py-int'] = 'python3.6';
        if ($this->getConfig('temp-dir') === false)
            $this->configuration['temp-dir'] = '.';
    }

    /**
     * Print help
     */
    private function printHelp() {
        echo 'Testovaci ramec pro testovani prekladace a interpretu jazyka IPPcode18.' . PHP_EOL;
        echo PHP_EOL;
        echo 'POUZITI:' . PHP_EOL;
        echo '    php test.php [MOZNOSTI]' . PHP_EOL;
        echo 'MOZNOSTI:' . PHP_EOL;
        echo '    -h, --help                   Vypise tuto napovedu' . PHP_EOL;
        echo '    -d, --directory <adresar>    Testy bude hledat v zadanem adresari (chybi-li tento parametr, tak skript prochazi aktualni adresar)' . PHP_EOL;
        echo '    -r, --recursive              Testy bude hledat i v podadresarich' . PHP_EOL;
        echo '    -p  --parse-script <soubor>  Soubor se skriptem v PHP pro analyzu zdrojoveho kodu (vychozi parse.php v aktualnim adresari)' . PHP_EOL;
        echo '    -i, --int-script <soubor>    Soubor se skriptem v Python pro interpretaci XML reprezentace kodu IPPcode18 (vychozi interpret.py v aktualnim adresari)' . PHP_EOL;
        echo '        --php-int <interpret>    Pouzije zadany interpret PHP (vychozi "php5.6")' . PHP_EOL;
        echo '        --py-int <interpret>     Pouzije zadany interpret Pythonu (vychozi "python3.6")' . PHP_EOL;
        echo '    -t, --temp-dir <adresar>     Pouzije zadany adresar pro docasne soubory, adresar musi existovat (vychozi je aktualni adresar)' . PHP_EOL;
        echo '        --text                   Vystup bude v jednoduche textove podobe' . PHP_EOL;
    }
}