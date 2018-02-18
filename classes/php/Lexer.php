<?php

final class Lexer {
    const CONTEXT_INSTRUCTION = 'inst';

    private $lang;
    private $context;
    private $inputStream;
    private $lineNum;
    private $arguments;
    private $argN;

    public function __construct(AddressCodeLang $lang, $inputStream=STDIN)
    {
        $this->lang = $lang;
        $this->context = $lang->getInitialContext();
        $this->inputStream = $inputStream;
        $this->lineNum = 0;
        $this->arguments = array();
        $this->argN = 0;
    }

    public function setContext($context) {
        $this->context = $context;
    }

    public function getNextToken() {
        if ($this->context === $this->lang->getHeader())
            return $this->getHeaderToken();
        else if ($this->lang->isValidOpcode($this->context))
            return $this->getArgToken();
        else if ($this->context === self::CONTEXT_INSTRUCTION)
            return $this->getOpcodeToken();
        else
            throw new InvalidContextException();
    }

    private function getHeaderToken() {
        $line = fgets($this->inputStream);
        $line = $this->stripComments($line);
        $line = rtrim($line);

        $this->lineNum++;

        if ($line === $this->lang->getHeader())
            return new Token(Token::HEADER, $line);
        else
            throw new LexicalErrorException(
                'Error on line '.$this->lineNum.': Expected "'.$this->lang->getHeader().'", got "'.$line.'".'
            );
    }

    private function getOpcodeToken() {
        $line = fgets($this->inputStream);

    }

    private function getArgToken() {

    }

    private function stripComments($string) {
        $arr = explode($this->lang->getCommentSeparator(), $string, 2);
        return $arr[0];
    }
}