<?php

/**
 * Class CodeAnalyzer
 *
 * Performs lexical and syntactic analysis for language IPPcode18
 */
final class CodeAnalyzer extends EventTrigger {
    /** Used in setContext() */
    const CONTEXT_INSTRUCTION = 'inst';

    // Event constants
    const EVENT_ON_COMMENT = 'onComment';
    const EVENT_ON_LOC = 'onLOC';

    /** @var IPPcode18  */
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

    /**
     * CodeAnalyzer constructor.
     * @param IPPcode18 $lang IPPcode18 instance
     * @param resource $inputStream input stream to perform analysis on (default STDIN)
     */
    public function __construct(IPPcode18 $lang, $inputStream=STDIN)
    {
        $this->lang = $lang;
        $this->context = $lang->getInitialContext();
        $this->inputStream = $inputStream;
        $this->lineNum = 0;
        $this->arguments = array();
        $this->argN = 0;
    }

    /**
     * Set analysis context
     *
     * @param $context string current instruction context or header
     */
    public function setContext($context) {
        $this->context = $context;
    }

    /**
     * Get next analysed token
     *
     * @return Token
     * @throws InvalidContextException
     */
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

    /**
     * Get current argument order
     *
     * @return int argument order
     */
    public function getArgumentOrder() {
        return $this->argN;
    }

    /**
     * Analyse header
     *
     * @return Token
     * @throws LexicalErrorException
     */
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

    /**
     * Analyse operation code
     *
     * @return Token
     * @throws LexicalErrorException
     */
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
                $this->notify(self::EVENT_ON_LOC);
                return $opcodeToken;
            } else {
                throw new LexicalErrorException($this->getErrorMsg('opcode', $opcode));
            }
        } else {
            return new Token(Token::EOL);
        }
    }

    /**
     * Analyse argument
     *
     * @return Token
     * @throws SyntaxErrorException
     */
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
        } else if ($this->lang->isValidArgument($argType, $this->arguments[$this->argN])) {
            $argToken = $this->lang->getArgumentToken($argType, $this->arguments[$this->argN]);
            unset($this->arguments[$this->argN]);
            return $argToken;
        }
        throw new RuntimeException();
    }

    /**
     * Trim line from comments and spaces
     *
     * @param string $line
     * @return string trimmed line
     */
    private function trimLine($line) {
        $line = $this->stripComments($line);
        return trim($line);
    }

    /**
     * Remove comments from string
     *
     * @param string $string
     * @return string
     */
    private function stripComments($string) {
        $arr = explode($this->lang->getCommentSeparator(), $string, 2);
        if ($arr[0] !== $string)
            $this->notify(self::EVENT_ON_COMMENT);
        return $arr[0];
    }

    /**
     * Get formatted error message
     *
     * @param $expected string
     * @param $actual string
     * @return string message
     */
    private function getErrorMsg($expected, $actual) {
        return 'Error on line '.$this->lineNum.': Expected '.$expected.', got "'.$actual.'"".';
    }
}