<?php

use PHPUnit\Framework\TestCase;

class IPPcode18Test extends TestCase {

    /** @var  IPPcode18 instance */
    private $lang;

    public function setUp()
    {
        $this->lang = new IPPcode18();
    }

    public function testGetHeader() {
        $this->assertSame('.IPPcode18', $this->lang->getHeader());
    }

    public function testCommentSeparator() {
        $this->assertSame('#', $this->lang->getCommentSeparator());
    }

    public function testSplitInstruction() {
        $expected = ['OPCODE', 'arg1', 'arg2', 'arg3'];

        $this->assertEquals($expected, $this->lang->splitInstruction("OPCODE\targ1   \t  arg2  arg3"));
    }

    public function testSplitInstructionNoArgs() {
        $expected = ['OPCODE'];

        $this->assertEquals($expected, $this->lang->splitInstruction('OPCODE'));
    }

    public function testValidOpcodes() {
        $this->assertTrue($this->lang->isValidOpcode('DEFVAR'));
        $this->assertTrue($this->lang->isValidOpcode('MOVE'));
        $this->assertTrue($this->lang->isValidOpcode('TYPE'));
        $this->assertTrue($this->lang->isValidOpcode('BREAK'));
        $this->assertTrue($this->lang->isValidOpcode('labEl'));
        $this->assertTrue($this->lang->isValidOpcode('JumP'));
    }

    public function testInvalidOpcodes() {
        $this->assertFalse($this->lang->isValidOpcode('INVALID'));
        $this->assertFalse($this->lang->isValidOpcode('  DEFvar'));
    }

    public function testValidVariables() {
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'LF@var'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'GF@var1aBle'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'TF@var'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'LF@_var'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'TF@v'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'GF@_-$&%*'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'TF@*VAR*'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'TF@&&1515'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'TF@var1'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'TF@fd0'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'TF@asRt987654'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'TF@asRt987654dfd5as1vd5sa6f4f5as6d1v6as5d*_-__-__-$$&%%%*this1sveeerryLONGG'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'GF@----'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'TF@&&&&&'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'TF@*'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'TF@$-_--__$'));
    }

    public function testInvalidVariables() {
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_VAR, ''));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'UF@var'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'GD@var'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'L-@var'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'LLF@var'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'TTF@var'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'GGF@var'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'LFF@var'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_VAR, '@var1'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'F@var'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'TF@1var'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'TF@'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'GF@va r'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'TF@&&&,&&'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'TF@va!r'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'TF@\\065'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_VAR, 'TF@proměnná'));
    }

    public function testValidLabel() {
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_LABEL, 'var'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_LABEL, 'var1aBle'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_LABEL, 'var'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_LABEL, '_var'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_LABEL, 'v'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_LABEL, '_-$&%*'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_LABEL, '*VAR*'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_LABEL, '&&1515'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_LABEL, 'var1'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_LABEL, 'fd0'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_LABEL, 'asRt987654'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_LABEL, 'asRt987654dfd5as1vd5sa6f4f5as6d1v6as5d*_-__-__-$$&%%%*this1sveeerryLONGG'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_LABEL, '----'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_LABEL, '&&&&&'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_LABEL, '*'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_LABEL, '$-_--__$'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_LABEL, 'DefvAr'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_LABEL, 'LABEL'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_LABEL, 'jump'));
    }

    public function testInvalidLabels() {
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_LABEL, '1var'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_LABEL, 'va r'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_LABEL, '&&&,&&'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_LABEL, 'va!r'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_LABEL, '\\065'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_LABEL, 'háčky'));
    }

    public function testValidConstInt() {
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'int@0'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'int@-1'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'int@-24243909'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'int@164984'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'int@+164984'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'int@00651'));
    }

    public function testValidConstBool() {
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'bool@true'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'bool@false'));
    }

    public function testInvalidConstBool() {
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'bl@true'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'BOOL@false'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'bOOl@false'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'bool@TRUE'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'bool@FALSE'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'bool@True'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'bool@False'));
    }

    public function testValidConstString() {
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'string@HelloWorld'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'string@HelloWorld!'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'string@ĚŠČŘŽÝýžřčžýáí'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'string@<>&/\'%(/!"!_"'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'string@'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'string@esc\\032ape'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'string@\\03232'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'string@\\999\\000'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'string@\\127fdsa'));
    }

    public function testInvalidConstString() {
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'STRING@'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'string@Hello World!'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'string@str#ing'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'string@inv\\alid'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_SYMB, 'string@str'.PHP_EOL.'ing'));
        $this->assertFalse($this->lang->isValidAddress(IPPcode18::ARG_SYMB, "string@str\ting"));
    }

    public function testValidType() {
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_TYPE, 'int'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_TYPE, 'string'));
        $this->assertTrue($this->lang->isValidAddress(IPPcode18::ARG_TYPE, 'bool'));
    }

    public function testGetAddressTokenVar() {
        $expected = new Token(Token::ARG_VAR, 'GF@var');

        $this->assertEquals($expected, $this->lang->getAddressToken(IPPcode18::ARG_VAR, 'GF@var'));
        $this->assertEquals($expected, $this->lang->getAddressToken(IPPcode18::ARG_SYMB, 'GF@var'));
    }

    public function testGetAddressTokenConstInt() {
        $expected = new Token(Token::ARG_INT, '-9');

        $this->assertEquals($expected, $this->lang->getAddressToken(IPPcode18::ARG_SYMB, 'int@-9'));
    }

    public function testGetAddressTokenConstBool() {
        $expected = new Token(Token::ARG_BOOL, 'true');

        $this->assertEquals($expected, $this->lang->getAddressToken(IPPcode18::ARG_SYMB, 'bool@true'));
    }

    public function testGetAddressTokenConstString() {
        $expected = new Token(Token::ARG_STRING, 'AhršěřOFso');

        $this->assertEquals($expected, $this->lang->getAddressToken(IPPcode18::ARG_SYMB, 'string@AhršěřOFso'));
    }

    public function testGetAddressTokenConstEmptyString() {
        $expected = new Token(Token::ARG_STRING, '');

        $this->assertEquals($expected, $this->lang->getAddressToken(IPPcode18::ARG_SYMB, 'string@'));
    }

    public function testGetAddressTokenLabel() {
        $expected = new Token(Token::ARG_LABEL, 'labEL1');

        $this->assertEquals($expected, $this->lang->getAddressToken(IPPcode18::ARG_LABEL, 'labEL1'));
    }

    public function testGetAddressTokenType() {
        $expected = new Token(Token::ARG_TYPE, 'int');

        $this->assertEquals($expected, $this->lang->getAddressToken(IPPcode18::ARG_TYPE, 'int'));
    }

    public function testGetArgumentType() {
        $this->assertSame(IPPcode18::ARG_SYMB, $this->lang->getArgumentType('WRITE', 1));
    }

    public function testGetArgumentTypeInvalid() {
        $this->assertSame(null, $this->lang->getArgumentType('WRITE', 2));
    }
}