<?php

class Token {
    const HEADER = 0;
    const OPCODE = 1;
    const ARG = 2;
    const EOF = 3;

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