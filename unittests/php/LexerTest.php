<?php

use PHPUnit\Framework\TestCase;

class LexerTest extends TestCase {

    private $stream;
    private $lexer;

    public function setUp()
    {
        $this->stream = fopen('php://memory', 'w+');
        $this->lexer = new Lexer($this->stream);
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
        $this->writeInputStream(Lexer::HEADER);
        $this->resetStream();

        $expected = new Token(Token::HEADER, Lexer::HEADER);

        $this->assertEquals($expected, $this->lexer->getNextToken());
    }

    public function testInvalidHeader() {
        $this->writeInputStream('Invalid');
        $this->resetStream();

        $this->expectException(LexicalErrorException::class);
        $this->lexer->getNextToken();
    }

    public function testHeaderWithComment() {
        $this->writeInputStream(Lexer::HEADER.'#comment');
        $this->resetStream();

        $expected = new Token(Token::HEADER, Lexer::HEADER);

        $this->assertEquals($expected, $this->lexer->getNextToken());
    }

    public function testHeaderEmptyInput() {
        $this->writeInputStream('');
        $this->resetStream();

        $this->expectException(LexicalErrorException::class);
        $this->lexer->getNextToken();
    }

    public function testHeaderSpaceComment() {
        $this->writeInputStream(Lexer::HEADER.'   # a comment');
        $this->resetStream();

        $expected = new Token(Token::HEADER, Lexer::HEADER);

        $this->assertEquals($expected, $this->lexer->getNextToken());
    }

    public function testHeaderWithLeadingSpaces() {
        $this->writeInputStream('   '.Lexer::HEADER);
        $this->resetStream();

        $this->expectException(LexicalErrorException::class);

        $this->lexer->getNextToken();
    }
}