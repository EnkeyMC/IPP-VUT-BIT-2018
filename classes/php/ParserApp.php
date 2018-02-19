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

        return ExitCodes::SUCCESS;
    }

    protected function loadConfiguration() {
        $argParser = new ArgParser(self::OPTIONS);
        $this->configuration = $argParser->parseArguments();
    }

    private function printHelp() {
        echo 'Parse source code in programming language IPPcode18 from standard input and output it to standard output as XML for further interpretation' . PHP_EOL;
        echo PHP_EOL;
        echo 'USAGE:' . PHP_EOL;
        echo '    php parse.php [OPTIONS]' . PHP_EOL;
        echo 'OPTIONS:' . PHP_EOL;
        echo '    -h, --help          Print this help' . PHP_EOL;
        echo '    -s, --src <file>    Specify source file' . PHP_EOL;
        echo '    -o, --out <file>    Specify output file' . PHP_EOL;
        echo '        --stats <file>  Specify file for code statistics' . PHP_EOL;
        echo '    -l, --loc           Print number of line of code to file specified in --stats' . PHP_EOL;
        echo '    -c, --comments      Print number of comments to file specified in --stats' . PHP_EOL;
    }

    private function initDependencies() {
        try {
            $this->inputStream = $this->getInputStream();
        } catch (Exception $e) {
            fwrite(STDERR, $e->getMessage());
            return ExitCodes::ERROR_OPENING_FILE_IN;
        }

        try {
            $this->outputStream = $this->getOutputStream();
        } catch (Exception $e) {
            fwrite(STDERR, $e->getMessage());
            return ExitCodes::ERROR_OPENING_FILE_OUT;
        }

        $lang = new IPPcode18();
        $this->codeAnalyzer = new CodeAnalyzer($lang, $this->inputStream);
        $this->xmlOutput = new XMLOutput();

        return ExitCodes::SUCCESS;
    }

    private function getInputStream() {
        $stream = STDIN;
        $src = $this->getConfig('src');
        if ($src !== false) {
            $stream = fopen($src, 'r');

            if ($stream === false) {
                throw new Exception('Failed to open file: '.$src);
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
                throw new Exception('Failed to open file: '.$out);
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
}