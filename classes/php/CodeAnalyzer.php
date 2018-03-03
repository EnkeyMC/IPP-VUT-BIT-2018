<?php

/**
 * Class CodeAnalyzer
 *
 * Performs lexical and syntactic analysis for language IPPcode18
 */
final class CodeAnalyzer extends EventTrigger {
    /** Used in setContext() */
    const CONTEXT_INSTRUCTION = 'inst';
    const CONTEXT_HEADER = 'header';

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
        $this->context = self::CONTEXT_HEADER;
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
        if ($this->context === self::CONTEXT_HEADER)
            return $this->getHeaderToken();
        else if ($this->lang->isValidOpcode($this->context))
            return $this->getArgToken();
        else if ($this->context === self::CONTEXT_INSTRUCTION)
            return $this->getOpcodeToken();
        else
            throw new InvalidContextException('Invalid context: '.$this->context, ExitCodes::ERROR_INTERN);
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
            throw new LexicalErrorException($this->getErrorMsg($this->lang->getHeader(), 'EOF'), ExitCodes::ERROR_LEX_SYNT);

        $line = fgets($this->inputStream);
        $line = $this->trimLine($line);
        $this->setContext(self::CONTEXT_INSTRUCTION);
        $this->lineNum++;

        if ($this->lang->isValidHeader($line))
            return new Token(Token::HEADER, $line);
        else
            throw new LexicalErrorException($this->getErrorMsg(IPPcode18::HEADER, $line), ExitCodes::ERROR_LEX_SYNT);
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
        $this->lineNum++;

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
                throw new LexicalErrorException($this->getErrorMsg('opcode', $opcode), ExitCodes::ERROR_LEX_SYNT);
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
     * @throws LexicalErrorException
     */
    private function getArgToken() {
        $this->argN++;
        $argType = $this->lang->getArgumentType($this->context, $this->argN);

        if ($argType === null xor empty($this->arguments)) {
            throw new SyntaxErrorException(
                $this->getErrorMsg(
                    empty($this->arguments) ? 'argument' : 'end of line',
                    empty($this->arguments) ? '' : $this->arguments[$this->argN]
                ),
                ExitCodes::ERROR_LEX_SYNT
            );
        } else if ($argType === null) {
            $this->setContext(self::CONTEXT_INSTRUCTION);
            return new Token(Token::EOL);
        } else if ($this->lang->isValidArgument($argType, $this->arguments[$this->argN])) {
            $argToken = $this->lang->getArgumentToken($argType, $this->arguments[$this->argN]);
            unset($this->arguments[$this->argN]);
            return $argToken;
        } else {
            throw new LexicalErrorException($this->getErrorMsg('valid argument',$this->arguments[$this->argN]), ExitCodes::ERROR_LEX_SYNT);
        }
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
        return 'Error on line '.$this->lineNum.': Expected '.$expected.', got "'.$actual.'".';
    }
}