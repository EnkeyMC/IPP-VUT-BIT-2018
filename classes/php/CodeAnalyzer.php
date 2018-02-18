<?php

final class CodeAnalyzer {
    const CONTEXT_INSTRUCTION = 'inst';

    /** @var AddressCodeLang  */
    private $lang;
    /** @var  string current context */
    private $context;
    /** @var resource input stream */
    private $inputStream;
    /** @var int current source code line number */
    private $lineNum;
    /** @var array list of arguments in currently processed instruction */
    private $arguments;
    /** @var int current argument number */
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
        if (feof($this->inputStream))
            throw new LexicalErrorException($this->getErrorMsg($this->lang->getHeader(), 'EOF'));

        $line = fgets($this->inputStream);
        $line = $this->trimLine($line);
        $this->setContext(self::CONTEXT_INSTRUCTION);
        $this->lineNum++;

        if ($line === $this->lang->getHeader())
            return new Token(Token::HEADER, $line);
        else
            throw new LexicalErrorException($this->getErrorMsg($this->lang->getHeader(), $line));
    }

    private function getOpcodeToken() {
        if (feof($this->inputStream))
            return new Token(Token::EOF);
        $line = fgets($this->inputStream);
        $line = $this->trimLine($line);

        if ($line !== '') {
            $this->arguments = $this->lang->splitInstruction($line);
            $opcode = $this->arguments[0];
            unset($this->arguments[0]);

            if ($this->lang->isValidOpcode($opcode)) {
                $this->argN = 0;
                $opcodeToken = $this->lang->getOpcodeToken($opcode);
                $this->setContext($opcodeToken->getData());
                return $opcodeToken;
            } else {
                throw new LexicalErrorException($this->getErrorMsg('opcode', $opcode));
            }
        } else {
            return new Token(Token::EOL);
        }
    }

    private function getArgToken() {
        $this->argN++;
        $argType = $this->lang->getArgumentType($this->context, $this->argN);

        if ($argType === null xor empty($this->arguments)) {
            throw new SyntaxErrorException($this->getErrorMsg(
                empty($this->arguments) ? 'argument' : 'end of line',
                empty($this->arguments) ? '' : $this->arguments[$this->argN])
            );
        } else if ($argType === null) {
            $this->setContext(self::CONTEXT_INSTRUCTION);
            return new Token(Token::EOL);
        } else if ($this->lang->isValidAddress($argType, $this->arguments[$this->argN])) {
            $argToken = $this->lang->getAddressToken($argType, $this->arguments[$this->argN]);
            unset($this->arguments[$this->argN]);
            return $argToken;
        }
        throw new RuntimeException();
    }

    private function trimLine($line) {
        $line = $this->stripComments($line);
        return trim($line);
    }

    private function stripComments($string) {
        $arr = explode($this->lang->getCommentSeparator(), $string, 2);
        return $arr[0];
    }

    private function getErrorMsg($expected, $actual) {
        return 'Error on line '.$this->lineNum.': Expected '.$expected.', got "'.$actual.'"".';
    }
}