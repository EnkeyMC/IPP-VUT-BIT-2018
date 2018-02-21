<?php

class ParserApp extends App
{
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

    public function run() {

        if ($this->getConfig('help')) {
            $this->printHelp();
            return ExitCodes::SUCCESS;
        }

        $rc = $this->initDependencies();
        if ($rc !== ExitCodes::SUCCESS)
            return $rc;

        try {
            $this->parse();
        } catch (SourceCodeException $e) {
            fwrite(STDERR, $e->getMessage());
            return ExitCodes::ERROR_LEX_SYNT;
        }

        fwrite($this->outputStream, $this->xmlOutput->getOutput());

        $this->closeInputStream();
        $this->closeOutputStream();

        $this->outputStatistics();

        return ExitCodes::SUCCESS;
    }

    protected function loadConfiguration() {
        $argParser = new ArgParser(self::OPTIONS);
        $this->configuration = $argParser->parseArguments();
        $this->checkArguments();
    }

    private function checkArguments() {
        //var_dump($this->configuration);
        // TODO
    }

    private function printHelp() {
        echo 'Prevede zdrojovy kod v jazyce IPPcode18 do XML reprezentace' . PHP_EOL;
        echo PHP_EOL;
        echo 'POUZITI:' . PHP_EOL;
        echo '    php parse.php [MOZNOSTI]' . PHP_EOL;
        echo 'MOZNOSTI:' . PHP_EOL;
        echo '    -h, --help            Vypise tuto napovedu' . PHP_EOL;
        echo '    -s, --src <soubor>    Urci vstupni soubor skriptu (vychozi STDIN)' . PHP_EOL;
        echo '    -o, --out <soubor>    Urci vystupni soubor skriptu (vychozi STDOUT)' . PHP_EOL;
        echo '        --stats <soubor>  Zapne sbirani statistik do zadaneho souboru' . PHP_EOL;
        echo '    -l, --loc             V pripade pouziti moznosti --stats zapne vypis statistiky radku kodu' . PHP_EOL;
        echo '    -c, --comments        V pripade pouziti moznosti --stats zapne vypis statistiky komentaru' . PHP_EOL;
    }

    private function initDependencies() {
        try {
            $this->inputStream = $this->getInputStream();
        } catch (OpenStreamException $e) {
            fwrite(STDERR, $e->getMessage());
            return ExitCodes::ERROR_OPENING_FILE_IN;
        }

        try {
            $this->outputStream = $this->getOutputStream();
        } catch (OpenStreamException $e) {
            fwrite(STDERR, $e->getMessage());
            return ExitCodes::ERROR_OPENING_FILE_OUT;
        }

        $lang = new IPPcode18();
        $this->codeAnalyzer = new CodeAnalyzer($lang, $this->inputStream);
        $this->xmlOutput = new XMLOutput();

        $this->statsCollector = new StatisticsCollector();
        $this->codeAnalyzer->attach($this->statsCollector);

        return ExitCodes::SUCCESS;
    }

    private function getInputStream() {
        $stream = STDIN;
        $src = $this->getConfig('src');
        if ($src !== false) {
            $stream = fopen($src, 'r');

            if ($stream === false) {
                throw new OpenStreamException('Failed to open file: '.$src);
            }
        }

        return $stream;
    }

    private function closeInputStream() {
        if ($this->getConfig('src') !== false)
            fclose($this->inputStream);
    }

    private function getOutputStream() {
        $stream = STDOUT;
        $out = $this->getConfig('out');
        if ($out !== false) {
            $stream = fopen($out, 'r');

            if ($stream === false) {
                throw new OpenStreamException('Failed to open file: '.$out);
            }
        }

        return $stream;
    }

    private function closeOutputStream() {
        if ($this->getConfig('out') !== false)
            fclose($this->outputStream);
    }

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
                if ($processingInst)
                    $this->xmlOutput->endInstruction();
            } else {
                $this->xmlOutput->addArgument($this->codeAnalyzer->getArgumentOrder(), $tokenType, $token->getData());
            }
        } while ($token->getType() !== Token::EOF);

        $this->xmlOutput->endOutput();
    }

    private function outputStatistics() {
        $statsFile = $this->getConfig('stats');
        if ($statsFile !== false) {
            $stream = fopen($statsFile, 'w');
            if ($stream === false) {
                throw new OpenStreamException('Failed to open file: '.$statsFile);
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