<?php

/**
 * Class Token
 *
 * Lexical token with token type and data
 */
class Token {
    // Token types
    const HEADER = 0;
    const OPCODE = 1;
    const EOL = 2;
    const EOF = 3;
    const ARG_VAR = 'var';
    const ARG_INT = 'int';
    const ARG_BOOL = 'bool';
    const ARG_STRING = 'string';
    const ARG_LABEL = 'label';
    const ARG_TYPE = 'type';

    /** @var  int|string token type */
    private $type;
    /** @var null|string additional token data */
    private $data;

    /**
     * Token constructor.
     * @param $type int|string token type
     * @param null|string $data additional token data
     */
    public function __construct($type, $data=null) {
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * @return int|string token type
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return null|string additional token data
     */
    public function getData() {
        return $this->data;
    }
}