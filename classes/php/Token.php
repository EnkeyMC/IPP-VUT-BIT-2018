<?php

class Token {
    const HEADER = 0;
    const OPCODE = 1;
    const EOF = 3;
    const ARG_VAR = 'var';
    const ARG_INT = 'int';
    const ARG_BOOL = 'bool';
    const ARG_STRING = 'string';
    const ARG_LABEL = 'label';
    const ARG_TYPE = 'type';

    private $type;
    private $data;

    public function __construct($type, $data=null) {
        $this->type = $type;
        $this->data = $data;
    }

    public function getType() {
        return $this->type;
    }

    public function getData() {
        return $this->data;
    }
}