<?php

use PHPUnit\Framework\TestCase;

class TokenTeset extends TestCase {
    public function testNoData() {
        $token = new Token(Token::HEADER);

        $this->assertSame(Token::HEADER, $token->getType());
        $this->assertSame(null, $token->getData());
    }

    public function testWithData() {
        $data = [1, 2, 3, 4];
        $token = new Token(Token::OPCODE, $data);

        $this->assertSame(Token::OPCODE, $token->getType());
        $this->assertSame($data, $token->getData());
    }
}