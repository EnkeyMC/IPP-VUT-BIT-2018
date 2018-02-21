<?php

use PHPUnit\Framework\TestCase;

class CodeAnalyzerTest extends TestCase {

    private $stream;

    /** @var  CodeAnalyzer instance */
    private $codeAnalyzer;

    public function setUp()
    {
        $this->stream = fopen('php://memory', 'w+');
        $this->codeAnalyzer = new CodeAnalyzer(new IPPcode18(), $this->stream);
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

        $this->assertEquals($expected, $this->codeAnalyzer->getNextToken());
    }

    public function testInvalidHeader() {
        $this->writeInputStream('Invalid');
        $this->resetStream();

        $this->expectException(LexicalErrorException::class);
        $this->codeAnalyzer->getNextToken();
    }

    public function testHeaderWithComment() {
        $this->writeInputStream(IPPcode18::HEADER.'#comment');
        $this->resetStream();

        $expected = new Token(Token::HEADER, IPPcode18::HEADER);

        $this->assertEquals($expected, $this->codeAnalyzer->getNextToken());
    }

    public function testHeaderEmptyInput() {
        $this->writeInputStream('');
        $this->resetStream();

        $this->expectException(LexicalErrorException::class);
        $this->codeAnalyzer->getNextToken();
    }

    public function testHeaderSpaceComment() {
        $this->writeInputStream(IPPcode18::HEADER.'   # a comment');
        $this->resetStream();

        $expected = new Token(Token::HEADER, IPPcode18::HEADER);

        $this->assertEquals($expected, $this->codeAnalyzer->getNextToken());
    }

    public function testHeaderWithLeadingSpaces() {
        $this->writeInputStream('   '.IPPcode18::HEADER);
        $this->resetStream();

        $expected = new Token(Token::HEADER, IPPcode18::HEADER);

        $this->assertEquals($expected, $this->codeAnalyzer->getNextToken());
    }

    public function testInstruction() {
        $this->writeInputStream('DEFvar GF@var');
        $this->resetStream();

        $this->codeAnalyzer->setContext(CodeAnalyzer::CONTEXT_INSTRUCTION);

        $expected = new Token(Token::OPCODE, 'DEFVAR');

        $this->assertEquals($expected, $this->codeAnalyzer->getNextToken());
    }

    public function testInvalidInstruction() {
        $this->writeInputStream('INVALID');
        $this->resetStream();
        $this->codeAnalyzer->setContext(CodeAnalyzer::CONTEXT_INSTRUCTION);

        $this->expectException(LexicalErrorException::class);

        $this->codeAnalyzer->getNextToken();
    }

    public function testEmptyLine() {
        $this->writeInputStream('   ');
        $this->resetStream();
        $this->codeAnalyzer->setContext(CodeAnalyzer::CONTEXT_INSTRUCTION);

        $expect = new Token(Token::EOL);

        $this->assertEquals($expect, $this->codeAnalyzer->getNextToken());
    }

    public function testSimpleArgs() {
        $this->writeInputStream('DEFVAR GF@var');
        $this->resetStream();

        $this->codeAnalyzer->setContext(CodeAnalyzer::CONTEXT_INSTRUCTION);

        $expected = new Token(Token::OPCODE, 'DEFVAR');
        $this->assertEquals($expected, $this->codeAnalyzer->getNextToken());

        $this->codeAnalyzer->setContext('DEFVAR');
        $expected = new Token(Token::ARG_VAR, 'GF@var');
        $this->assertEquals($expected, $this->codeAnalyzer->getNextToken());
        $expected = new Token(Token::EOL);
        $this->assertEquals($expected, $this->codeAnalyzer->getNextToken());
    }

    public function testThreeArgs() {
        $this->writeInputStream('MUL GF@var LF@var2 int@5');
        $this->resetStream();

        $this->codeAnalyzer->setContext(CodeAnalyzer::CONTEXT_INSTRUCTION);

        $this->assertNextToken(Token::OPCODE, 'MUL');

        $this->assertNextToken(Token::ARG_VAR, 'GF@var');
        $this->assertNextToken(Token::ARG_VAR, 'LF@var2');
        $this->assertNextToken(Token::ARG_INT, '5');
        $this->assertNextToken(Token::EOL);
    }

    public function testInvalidArgs() {
        $this->writeInputStream('PUSHFRAME int@4');
        $this->resetStream();

        $this->codeAnalyzer->setContext(CodeAnalyzer::CONTEXT_INSTRUCTION);

        $this->assertNextToken(Token::OPCODE, 'PUSHFRAME');

        $this->expectException(SyntaxErrorException::class);
        $this->codeAnalyzer->getNextToken();
    }

    public function testTooFewArgs() {
        $this->writeInputStream('MUL GF@var int@4');
        $this->resetStream();

        $this->codeAnalyzer->setContext(CodeAnalyzer::CONTEXT_INSTRUCTION);

        $this->assertNextToken(Token::OPCODE, 'MUL');
        $this->assertNextToken(Token::ARG_VAR, 'GF@var');
        $this->assertNextToken(Token::ARG_INT, '4');

        $this->expectException(SyntaxErrorException::class);
        $this->codeAnalyzer->getNextToken();
    }

    public function testSimpleProgram() {
        $this->writeInputStream('.IPPcode18'.PHP_EOL);
        $this->writeInputStream(PHP_EOL);
        $this->writeInputStream('CREATEFRAME'.PHP_EOL);
        $this->writeInputStream('defvar TF@var # create new variable'.PHP_EOL);
        $this->writeInputStream('# Initialize with true'.PHP_EOL);
        $this->writeInputStream('move TF@var bool@true'.PHP_EOL);
        $this->resetStream();

        $this->assertNextToken(Token::HEADER, '.IPPcode18');
        $this->assertNextToken(Token::EOL);
        $this->assertNextToken(Token::OPCODE, 'CREATEFRAME');
        $this->assertNextToken(Token::EOL);
        $this->assertNextToken(Token::OPCODE, 'DEFVAR');
        $this->assertNextToken(Token::ARG_VAR, 'TF@var');
        $this->assertNextToken(Token::EOL);
        $this->assertNextToken(Token::EOL);
        $this->assertNextToken(Token::OPCODE, 'MOVE');
        $this->assertNextToken(Token::ARG_VAR, 'TF@var');
        $this->assertNextToken(Token::ARG_BOOL, 'true');
        $this->assertNextToken(Token::EOL);
        $this->assertNextToken(Token::EOL);
        $this->assertNextToken(Token::EOF);
    }

    public function testInvalidContext() {
        $this->codeAnalyzer->setContext('invalid');

        $this->expectException(InvalidContextException::class);
        $this->codeAnalyzer->getNextToken();
    }

    private function assertNextToken($type, $data=null) {
        $this->assertEquals(new Token($type, $data), $this->codeAnalyzer->getNextToken());
    }

    public function testOnComment() {
        $listener = new EventListenerDummy();
        $this->codeAnalyzer->attach($listener);
        $this->codeAnalyzer->setContext(CodeAnalyzer::CONTEXT_INSTRUCTION);

        $this->writeInputStream('# new var');
        $this->resetStream();

        $this->codeAnalyzer->getNextToken();

        $this->assertSame(CodeAnalyzer::EVENT_ON_COMMENT, $listener->lastEvent);
    }

    public function testOnEmptyComment() {
        $listener = new EventListenerDummy();
        $this->codeAnalyzer->attach($listener);

        $this->writeInputStream('.IPPcode18 #');
        $this->resetStream();

        $this->codeAnalyzer->getNextToken();

        $this->assertSame(CodeAnalyzer::EVENT_ON_COMMENT, $listener->lastEvent);
    }

    public function testNoComment() {
        $listener = new EventListenerDummy();
        $this->codeAnalyzer->attach($listener);

        $this->writeInputStream('.IPPcode18  ');
        $this->resetStream();

        $this->codeAnalyzer->getNextToken();

        $this->assertNotSame(CodeAnalyzer::EVENT_ON_COMMENT, $listener->lastEvent);
    }

    public function testLOC() {
        $listener = new EventListenerDummy();
        $this->codeAnalyzer->attach($listener);
        $this->codeAnalyzer->setContext(CodeAnalyzer::CONTEXT_INSTRUCTION);

        $this->writeInputStream('defvar GF@var');
        $this->resetStream();

        $this->codeAnalyzer->getNextToken();

        $this->assertSame(CodeAnalyzer::EVENT_ON_LOC, $listener->lastEvent);
    }

    public function testNoLOC() {
        $listener = new EventListenerDummy();
        $this->codeAnalyzer->attach($listener);
        $this->codeAnalyzer->setContext(CodeAnalyzer::CONTEXT_INSTRUCTION);

        $this->writeInputStream('  ');
        $this->resetStream();

        $this->codeAnalyzer->getNextToken();

        $this->assertNotSame(CodeAnalyzer::EVENT_ON_LOC, $listener->lastEvent);
    }
}