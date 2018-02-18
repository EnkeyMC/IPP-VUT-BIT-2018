<?php

class IPPcode18 extends AddressCodeLang {

    const HEADER = '.IPPcode18';

    const COMMENT_SEPARATOR = '#';

    const ARG_VAR = 0;
    const ARG_SYMB = 1;
    const ARG_LABEL = 2;
    const ARG_TYPE = 3;

    const REGEX_LIST = [
        self::ARG_VAR => '/^[LTG]F@[a-zA-Z\-_&$*%][a-zA-Z\-_&$*%0-9]*$/',
        self::ARG_SYMB => '/^([LTG]F@[a-zA-Z\-_&$*%][a-zA-Z\-_&$*%\d]*|(int)@([^\s]+)|(bool)@(true|false)|(string)@(([^\s#\\\\]*(\\\\\d\d\d)?)*))$/u',
        self::ARG_LABEL => '/^[a-zA-Z\-_&$*%][a-zA-Z\-_&$*%0-9]*$/',
        self::ARG_TYPE => '/^(int|string|bool)$/'
    ];

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

    public function getInitialContext()
    {
        return self::HEADER;
    }

    public function getHeader()
    {
        return self::HEADER;
    }

    public function getCommentSeparator()
    {
        return self::COMMENT_SEPARATOR;
    }

    public function isValidOpcode($opcode)
    {
        $opcode = strtoupper($opcode);
        return array_key_exists($opcode, self::INSTRUCTION_LIST);
    }

    public function getOpcodeToken($opcode) {
        return new Token(Token::OPCODE, strtoupper($opcode));
    }

    public function isValidAddress($addrType, $addr)
    {
        if (!array_key_exists($addrType, self::REGEX_LIST))
            throw new InvalidAddressTypeException('Invalid address type given to IPPcode18::isValidAddress() ('.$addrType.')');

        $rv = preg_match(self::REGEX_LIST[$addrType], $addr);
        if ($rv === false)
            throw new RegexErrorException('Error occured during matching of regex: ' . self::REGEX_LIST[$addrType]);

        return $rv === 1 ? true : false;
    }

    public function getAddressToken($addrType, $addr)
    {
        $matchGroups = array();
        preg_match(self::REGEX_LIST[$addrType], $addr, $matchGroups);

        return $this->getTokenFromMatchGroups($addrType, $matchGroups);
    }

    private function getTokenFromMatchGroups($addrType, $matchGroups) {
        switch ($addrType) {
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

    public function splitInstruction($instruction)
    {
        return preg_split("/\\s+/", $instruction);
    }

    public function getArgumentType($opcode, $n)
    {
        if (array_key_exists(--$n, self::INSTRUCTION_LIST[$opcode]))
            return self::INSTRUCTION_LIST[$opcode][$n];
        else
            return null;
    }
}