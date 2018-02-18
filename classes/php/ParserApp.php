<?php

class ParserApp extends App
{
    const OPTIONS = ['help' => 'h', 'src:' => 's:'];

    const ERROR_CODE = 21;

    public function run() {
        if ($this->getConfig('help')) {
            $this->printHelp();
            return ExitCodes::SUCCESS;
        }

        $stream = STDIN;
        $src = $this->getConfig('src');
        if ($src !== false) {
            $stream = fopen($src, 'r');

            if ($stream === false) {
                throw new Exception('Failed to open file: '.$src);
            }
        }

        $codeAnalyzer = new CodeAnalyzer(new IPPcode18(), $stream);
        $xmlOutput = new XMLOutput();
        $xmlOutput->startOutput();
        $token = null;
        $processingInst = false;

        try {
            do {
                $token = $codeAnalyzer->getNextToken();
                $tokenType = $token->getType();

                if ($tokenType === Token::HEADER || $tokenType === Token::EOF) {
                } else if ($tokenType === Token::OPCODE) {
                    $processingInst = true;
                    $xmlOutput->startInstruction($token->getData());
                } else if ($tokenType === Token::EOL) {
                    if ($processingInst)
                        $xmlOutput->endInstruction();
                } else {
                    $xmlOutput->addArgument($codeAnalyzer->getArgumentOrder(), $tokenType, $token->getData());
                }
            } while ($token->getType() !== Token::EOF);
        } catch (SourceCodeException $e) {
            fwrite(STDERR, $e->getMessage());
            return ExitCodes::ERROR_LEX_SYNT;
        }

        $xmlOutput->endOutput();

        echo $xmlOutput->getOutput();

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
        echo '    php parse.php [OPTION]' . PHP_EOL;
        echo 'OPTIONS:' . PHP_EOL;
        echo '    -h, --help    Print this help' . PHP_EOL;
    }
}