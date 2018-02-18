<?php

use PHPUnit\Framework\TestCase;

class LexerTest extends TestCase {

    private $stream;
    private $lexer;

    public function setUp()
    {
        $this->stream = fopen('php://memory', 'w+');
        $this->lexer = new Lexer(new IPPcode18(), $this->stream);
    }

    public function tearDown()
    {
        fclose($this->stream);
    }

    private function writeInputStream($string) {
        fwrite($this->stream, $string);
    }

    private function resetStream() {
        fseek($this->stream, 0);
    }

    public function testHeaderToken() {
        $this->writeInputStream(IPPcode18::HEADER);
        $this->resetStream();

        $expected = new Token(Token::HEADER, IPPcode18::HEADER);

        $this->assertEquals($expected, $this->lexer->getNextToken());
    }

    public function testInvalidHeader() {
        $this->writeInputStream('Invalid');
        $this->resetStream();

        $this->expectException(LexicalErrorException::class);
        $this->lexer->getNextToken();
    }

    public function testHeaderWithComment() {
        $this->writeInputStream(IPPcode18::HEADER.'#comment');
        $this->resetStream();

        $expected = new Token(Token::HEADER, IPPcode18::HEADER);

        $this->assertEquals($expected, $this->lexer->getNextToken());
    }

    public function testHeaderEmptyInput() {
        $this->writeInputStream('');
        $this->resetStream();

        $this->expectException(LexicalErrorException::class);
        $this->lexer->getNextToken();
    }

    public function testHeaderSpaceComment() {
        $this->writeInputStream(IPPcode18::HEADER.'   # a comment');
        $this->resetStream();

        $expected = new Token(Token::HEADER, IPPcode18::HEADER);

        $this->assertEquals($expected, $this->lexer->getNextToken());
    }

    public function testHeaderWithLeadingSpaces() {
        $this->writeInputStream('   '.IPPcode18::HEADER);
        $this->resetStream();

        $this->expectException(LexicalErrorException::class);

        $this->lexer->getNextToken();
    }

    public function testInstruction() {
        $this->writeInputStream('DEFvar GF@var');
        $this->resetStream();

        $this->lexer->setContext(Lexer::CONTEXT_INSTRUCTION);

        $expected = new Token(Token::OPCODE, 'DEFVAR');

        $this->assertEquals($expected, $this->lexer->getNextToken());
    }

    public function testInvalidInstruction() {
        $this->writeInputStream('INVALID');
        $this->resetStream();
        $this->lexer->setContext(Lexer::CONTEXT_INSTRUCTION);

        $this->expectException(LexicalErrorException::class);

        $this->lexer->getNextToken();
    }
}