<?php

/**
 * Class IPPcode18
 *
 * Describes IPPcode18 language
 */
class IPPcode18 {

    const HEADER = '.IPPcode18';

    const COMMENT_SEPARATOR = '#';

    // Instruction argument types
    const ARG_VAR = 0;
    const ARG_SYMB = 1;
    const ARG_LABEL = 2;
    const ARG_TYPE = 3;

    /** List of regular expression to check validity of argument */
    const REGEX_LIST = [
        self::ARG_VAR => '/^[LTG]F@[a-zA-Z\-_&$*%][a-zA-Z\-_&$*%0-9]*$/',
        self::ARG_SYMB => '/^([LTG]F@[a-zA-Z\-_&$*%][a-zA-Z\-_&$*%\d]*|(int)@([^\s]+)|(bool)@(true|false)|(string)@(([^\s#\\\\]*(\\\\\d\d\d)?)*))$/u',
        self::ARG_LABEL => '/^[a-zA-Z\-_&$*%][a-zA-Z\-_&$*%0-9]*$/',
        self::ARG_TYPE => '/^(int|string|bool)$/'
    ];

    /** List of valid instructions and their argument types */
    const INSTRUCTION_LIST = [
        'MOVE' => [self::ARG_VAR, self::ARG_SYMB],
        'CREATEFRAME' => [],
        'PUSHFRAME' => [],
        'POPFRAME' => [],
        'DEFVAR' => [self::ARG_VAR],
        'CALL' => [self::ARG_LABEL],
        'RETURN' => [],
        'PUSHS' => [self::ARG_SYMB],
        'POPS' => [self::ARG_VAR],
        'ADD' => [self::ARG_VAR, self::ARG_SYMB, self::ARG_SYMB],
        'SUB' => [self::ARG_VAR, self::ARG_SYMB, self::ARG_SYMB],
        'MUL' => [self::ARG_VAR, self::ARG_SYMB, self::ARG_SYMB],
        'IDIV' => [self::ARG_VAR, self::ARG_SYMB, self::ARG_SYMB],
        'LT' => [self::ARG_VAR, self::ARG_SYMB, self::ARG_SYMB],
        'GT' => [self::ARG_VAR, self::ARG_SYMB, self::ARG_SYMB],
        'EQ' => [self::ARG_VAR, self::ARG_SYMB, self::ARG_SYMB],
        'AND' => [self::ARG_VAR, self::ARG_SYMB, self::ARG_SYMB],
        'OR' => [self::ARG_VAR, self::ARG_SYMB, self::ARG_SYMB],
        'NOT' => [self::ARG_VAR, self::ARG_SYMB],
        'INT2CHAR' => [self::ARG_VAR, self::ARG_SYMB],
        'STRI2INT' => [self::ARG_VAR, self::ARG_SYMB, self::ARG_SYMB],
        'READ' => [self::ARG_VAR, self::ARG_TYPE],
        'WRITE' => [self::ARG_SYMB],
        'CONCAT' => [self::ARG_VAR, self::ARG_SYMB, self::ARG_SYMB],
        'STRLEN' => [self::ARG_VAR, self::ARG_SYMB],
        'GETCHAR' => [self::ARG_VAR, self::ARG_SYMB, self::ARG_SYMB],
        'SETCHAR' => [self::ARG_VAR, self::ARG_SYMB, self::ARG_SYMB],
        'TYPE' => [self::ARG_VAR, self::ARG_SYMB],
        'LABEL' => [self::ARG_LABEL],
        'JUMP' => [self::ARG_LABEL],
        'JUMPIFEQ' => [self::ARG_LABEL, self::ARG_SYMB, self::ARG_SYMB],
        'JUMPIFNEQ' => [self::ARG_LABEL, self::ARG_SYMB, self::ARG_SYMB],
        'DPRINT' => [self::ARG_SYMB],
        'BREAK' => []
    ];

    /**
     * Get initial context for code analyser
     *
     * @return string initial context
     */
    public function getInitialContext()
    {
        return self::HEADER;
    }

    /**
     * Get language header
     *
     * @return string header
     */
    public function getHeader()
    {
        return self::HEADER;
    }

    /**
     * Get string that separates comments
     *
     * @return string comment separator
     */
    public function getCommentSeparator()
    {
        return self::COMMENT_SEPARATOR;
    }

    /**
     * Check if operation code is valid
     *
     * @param $opcode string
     * @return bool true if opcode is valid, false otherwise
     */
    public function isValidOpcode($opcode)
    {
        $opcode = strtoupper($opcode);
        return array_key_exists($opcode, self::INSTRUCTION_LIST);
    }

    /**
     * Create token from operation code
     *
     * @param $opcode string
     * @return Token
     */
    public function getOpcodeToken($opcode) {
        return new Token(Token::OPCODE, strtoupper($opcode));
    }

    /**
     * Check if argument is valid
     *
     * @param $argType int type of argument
     * @param $arg string argument
     * @return bool true if valid, false otherwise
     * @throws InvalidAddressTypeException
     * @throws RegexErrorException
     */
    public function isValidArgument($argType, $arg)
    {
        if (!array_key_exists($argType, self::REGEX_LIST))
            throw new InvalidAddressTypeException('Invalid address type given to IPPcode18::isValidAddress() ('.$argType.')', ExitCodes::ERROR_INTERN);

        $rv = preg_match(self::REGEX_LIST[$argType], $arg);
        if ($rv === false)
            throw new RegexErrorException('Error occured during matching of regex: ' . self::REGEX_LIST[$argType], ExitCodes::ERROR_INTERN);

        return $rv === 1 ? true : false;
    }

    /**
     * Create token with information about given argument
     *
     * Expects given argument to be valid
     *
     * @param $argType int type of argument
     * @param $arg string argument
     * @return null|Token null on invalid argType
     */
    public function getArgumentToken($argType, $arg)
    {
        $matchGroups = array();
        preg_match(self::REGEX_LIST[$argType], $arg, $matchGroups);

        return $this->getTokenFromMatchGroups($argType, $matchGroups);
    }

    /**
     * Create token from regex match groups
     *
     * @param $argType int argument type
     * @param $matchGroups array of matched groups
     * @return null|Token null on invalid argType
     */
    private function getTokenFromMatchGroups($argType, array $matchGroups) {
        switch ($argType) {
            case self::ARG_VAR: {
                return new Token(Token::ARG_VAR, $matchGroups[0]);
            }
            case self::ARG_SYMB: {
                if (sizeof($matchGroups) < 3) {
                    return new Token(Token::ARG_VAR, $matchGroups[0]);
                } else {
                    $i = 2;

                    while ($matchGroups[$i] === '')
                        ++$i;

                    return new Token($matchGroups[$i], $matchGroups[$i + 1]);
                }
            }
            case self::ARG_LABEL: {
                return new Token(Token::ARG_LABEL, $matchGroups[0]);
            }
            case self::ARG_TYPE: {
                return new Token(Token::ARG_TYPE, $matchGroups[0]);
            }
        }

        return null;
    }

    /**
     * Split instruction to array of opcode and arguments
     *
     * @param $instruction string
     * @return array opcode (idx 0) and arguments
     */
    public function splitInstruction($instruction)
    {
        return preg_split("/\\s+/", $instruction);
    }

    /**
     * Get nth argument type for given operation code
     *
     * @param $opcode string valid opcode
     * @param $n int nth argument
     * @return null|int argument type, null if opcode doesn't have nth argument
     */
    public function getArgumentType($opcode, $n)
    {
        if (array_key_exists(--$n, self::INSTRUCTION_LIST[$opcode]))
            return self::INSTRUCTION_LIST[$opcode][$n];
        else
            return null;
    }
}