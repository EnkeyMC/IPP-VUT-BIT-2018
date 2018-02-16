<?php

final class Lexer {

    const HEADER = '.IPPcode18';

    const STATE_HEADER = 'getHeaderToken';
    const STATE_OPCODE = 'getOpcodeToken';
    const STATE_ARG = 'getArgToken';

    const OPCODE_LIST = [
        'MOVE' => 0,
        'CREATEFRAME' => 0,
        'PUSHFRAME' => 0,
        'POPFRAME' => 0,
        'DEFVAR' => 0,
        'CALL' => 0,
        'RETURN' => 0,
        'PUSHS' => 0,
        'POPS' => 0,
        'ADD' => 0,
        'SUB' => 0,
        'MUL' => 0,
        'IDIV' => 0,
        'LT' => 0,
        'GT' => 0,
        'EQ' => 0,
        'AND' => 0,
        'OR' => 0,
        'NOT' => 0,
        'INT2CHAR' => 0,
        'STRI2INT' => 0,
        'READ' => 0,
        'WRITE' => 0,
        'CONCAT' => 0,
        'STRLEN' => 0,
        'GETCHAR' => 0,
        'SETCHAR' => 0,
        'TYPE' => 0,
        'LABEL' => 0,
        'JUMP' => 0,
        'JUMPIFEQ' => 0,
        'JUMPIFNEQ' => 0,
        'DPRINT' => 0,
        'BREAK' => 0
    ];

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