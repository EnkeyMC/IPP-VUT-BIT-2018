<?php

final class Lexer {

    const HEADER = '.IPPcode18';

    const STATE_HEADER = 'getHeaderToken';
    const STATE_OPCODE = 'getOpcodeToken';
    const STATE_ARG = 'getArgToken';

    private $state;
    private $inputStream;
    private $lineNum;

    public function __construct($inputStream)
    {
        $this->state = self::STATE_HEADER;
        $this->inputStream = $inputStream;
        $this->lineNum = 0;
    }

    public function setState($state) {
        $this->state = $state;
    }

    public function getNextToken() {
        if (method_exists($this, $this->state)) {
            return call_user_func([$this, $this->state]);
        } else {
            throw new InvalidStateException();
        }
    }

    private function getHeaderToken() {
        $line = fgets($this->inputStream);
        $line = $this->stripComments($line);
        $line = rtrim($line);

        $this->lineNum++;

        if ($line === self::HEADER)
            return new Token(Token::HEADER, $line);
        else
            throw new LexicalErrorException(
                'Error on line '.$this->lineNum.': Expected "'.self::HEADER.'", got "'.$line.'".'
            );
    }

    private function getOpcodeToken() {

    }

    private function getArgToken() {

    }

    private function stripComments($string) {
        $arr = explode('#', $string, 2);
        return $arr[0];
    }
}