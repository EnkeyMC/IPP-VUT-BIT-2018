<?php

/**
 * Class ParserApp
 *
 * App for parsing IPPcode18 and outputting XML representation
 */
class ParserApp extends App
{
    /** Valid command line options */
    const OPTIONS = [
        'help' => 'h',
        'src:' => 's:',
        'out:' => 'o:',
        'stats:' => '',
        'loc' => 'l',
        'comments' => 'c'
    ];

    /** @var  resource */
    private $inputStream;
    /** @var resource */
    private $outputStream;
    /** @var  CodeAnalyzer */
    private $codeAnalyzer;
    /** @var  XMLOutput */
    private $xmlOutput;
    /** @var  StatisticsCollector */
    private $statsCollector;

    /**
     * Run application
     *
     * @return int exit code
     */
    public function run() {
        if ($this->getConfig('help')) {
            $this->printHelp();
            return ExitCodes::SUCCESS;
        }

        $this->initDependencies();

        $this->parse();

        fwrite($this->outputStream, $this->xmlOutput->getOutput());

        $this->closeInputStream();
        $this->closeOutputStream();

        $this->outputStatistics();

        return ExitCodes::SUCCESS;
    }

    /**
     * Load application configuration
     */
    protected function loadConfiguration() {
        $argParser = new ArgParser(self::OPTIONS);
        $this->configuration = $argParser->parseArguments();
        $this->checkArguments();
    }

    /**
     * Check argument combinations
     *
     * @throws InvalidArgumentException
     */
    private function checkArguments() {
        if ($this->getConfig('help') && sizeof($this->configuration) > 1)
            throw new InvalidArgumentException('No other option can be used with option "help"', ExitCodes::ERROR_PARAMETER);
        if (($this->getConfig('loc') || $this->getConfig('comments')) && $this->getConfig('stats') === false)
            throw new InvalidArgumentException('Option "loc" or "comments" cannot be used without option "stats"', ExitCodes::ERROR_PARAMETER);
    }

    /**
     * Print help
     */
    private function printHelp() {
        echo 'Prevede zdrojovy kod v jazyce IPPcode18 do XML reprezentace' . PHP_EOL;
        echo PHP_EOL;
        echo 'POUZITI:' . PHP_EOL;
        echo '    php parse.php [MOZNOSTI]' . PHP_EOL;
        echo 'MOZNOSTI:' . PHP_EOL;
        echo '    -h, --help            Vypise tuto napovedu' . PHP_EOL;
        echo '    -s, --src=<soubor>    Urci vstupni soubor skriptu (vychozi STDIN)' . PHP_EOL;
        echo '    -o, --out=<soubor>    Urci vystupni soubor skriptu (vychozi STDOUT)' . PHP_EOL;
        echo '        --stats=<soubor>  Zapne sbirani statistik do zadaneho souboru' . PHP_EOL;
        echo '    -l, --loc             V pripade pouziti moznosti --stats zapne vypis statistiky radku kodu' . PHP_EOL;
        echo '    -c, --comments        V pripade pouziti moznosti --stats zapne vypis statistiky komentaru' . PHP_EOL;
    }

    /**
     * Initialize dependencies and streams needed for parsing
     *
     * @return int exit code
     */
    private function initDependencies() {
        $this->inputStream = $this->getInputStream();

        $this->outputStream = $this->getOutputStream();

        $lang = new IPPcode18();
        $this->codeAnalyzer = new CodeAnalyzer($lang, $this->inputStream);
        $this->xmlOutput = new XMLOutput();

        $this->statsCollector = new StatisticsCollector();
        $this->codeAnalyzer->attach($this->statsCollector);

        return ExitCodes::SUCCESS;
    }

    /**
     * Open input stream
     *
     * @return resource
     * @throws OpenStreamException
     */
    private function getInputStream() {
        $stream = STDIN;
        $src = $this->getConfig('src');
        if ($src !== false) {
            $stream = fopen($src, 'r');

            if ($stream === false) {
                throw new OpenStreamException('Failed to open file: '.$src, ExitCodes::ERROR_OPENING_FILE_IN);
            }
        }

        return $stream;
    }

    /**
     * Close input stream
     */
    private function closeInputStream() {
        if ($this->getConfig('src') !== false)
            fclose($this->inputStream);
    }

    /**
     * Open output stream
     *
     * @return resource
     * @throws OpenStreamException
     */
    private function getOutputStream() {
        $stream = STDOUT;
        $out = $this->getConfig('out');
        if ($out !== false) {
            $stream = fopen($out, 'r');

            if ($stream === false) {
                throw new OpenStreamException('Failed to open file: '.$out, ExitCodes::ERROR_OPENING_FILE_OUT);
            }
        }

        return $stream;
    }

    /**
     * Close output stream
     */
    private function closeOutputStream() {
        if ($this->getConfig('out') !== false)
            fclose($this->outputStream);
    }

    /**
     * Parse source file
     */
    private function parse() {
        $this->xmlOutput->startOutput();
        $token = null;
        $processingInst = false;

        do {
            $token = $this->codeAnalyzer->getNextToken();
            $tokenType = $token->getType();

            if ($tokenType === Token::HEADER || $tokenType === Token::EOF) {
            } else if ($tokenType === Token::OPCODE) {
                $processingInst = true;
                $this->xmlOutput->startInstruction($token->getData());
            } else if ($tokenType === Token::EOL) {
                if ($processingInst) {
                    $this->xmlOutput->endInstruction();
                    $processingInst = false;
                }
            } else {
                $this->xmlOutput->addArgument($this->codeAnalyzer->getArgumentOrder(), $tokenType, $token->getData());
            }
        } while ($token->getType() !== Token::EOF);

        if ($processingInst)
            $this->xmlOutput->endInstruction();

        $this->xmlOutput->endOutput();
    }

    /**
     * Output statistics if configured
     *
     * @throws OpenStreamException
     */
    private function outputStatistics() {
        $statsFile = $this->getConfig('stats');
        if ($statsFile !== false) {
            $stream = fopen($statsFile, 'w');
            if ($stream === false) {
                throw new OpenStreamException('Failed to open file: '.$statsFile, ExitCodes::ERROR_OPENING_FILE_OUT);
            }

            if ($this->isOptionInOrder('loc', 'comments')) {
                if ($this->getConfig('loc'))
                    fwrite($stream, $this->statsCollector->getLOCStatistics().PHP_EOL);
                if ($this->getConfig('comments'))
                    fwrite($stream, $this->statsCollector->getCommentStatistics().PHP_EOL);
            } else {
                if ($this->getConfig('comments'))
                    fwrite($stream, $this->statsCollector->getCommentStatistics().PHP_EOL);
                if ($this->getConfig('loc'))
                    fwrite($stream, $this->statsCollector->getLOCStatistics().PHP_EOL);
            }

            fclose($stream);
        }
    }

    /**
     * Check if command line options were given in certain order
     *
     * @param $first string
     * @param $second string
     * @return bool
     */
    private function isOptionInOrder($first, $second) {
        foreach($this->configuration as $option => $value) {
            if ($option === $first)
                return true;
            if ($option === $second)
                return false;
        }

        return true;
    }
}