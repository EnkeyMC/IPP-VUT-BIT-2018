<?php

use PHPUnit\Framework\TestCase;

class ArgParserTest extends TestCase {

    public function testNoOptions() {
        $dummy = new ArgParser([]);
        $this->mockArgv([]);

        $this->assertSame([], $dummy->parseArguments());
    }

    public function testOnlyLongOptions() {
        $options = ['option' => '', 'option2' => ''];
        $dummy = new ArgParser($options);
        $this->mockArgv(['--option', '--option2']);

        $this->assertSame(
            ['option' => false, 'option2' => false],
            $dummy->parseArguments()
        );
    }

    public function testShortAliases() {
        $options = ['testShort' => 'o', 'testLong' => 't'];
        $dummy = new ArgParser($options);
        $this->mockArgv(['-o', '--testLong']);

        $this->assertSame(
            ['testShort' => false, 'testLong' => false],
            $dummy->parseArguments()
        );
    }

    public function testShortAliasesWithValue() {
        $options = ['opt1:' => 'o:', 'opt2::' => 't::'];
        $dummy = new ArgParser($options);
        $this->mockArgv(['-o=value', '--opt2=value']);

        $this->assertSame(
            ['opt1' => 'value', 'opt2' => 'value'],
            $dummy->parseArguments()
        );
    }

    public function testInvalidArgument() {
        $dummy = new ArgParser(['opt' => '']);
        $this->mockArgv(['arg']);

        $this->expectException(InvalidArgumentException::class);
        $dummy->parseArguments();
    }

    public function testInvalidOption() {
        $dummy = new ArgParser(['opt' => '']);
        $this->mockArgv(['--arg']);

        $this->expectException(InvalidArgumentException::class);
        $dummy->parseArguments();
    }

    public function testRequiredValueMissing() {
        $dummy = new ArgParser(['opt:' => '']);
        $this->mockArgv(['--opt']);

        $this->expectException(InvalidArgumentException::class);
        $dummy->parseArguments();
    }

    public function testOptionalValueMissing() {
        $dummy = new ArgParser(['opt::' => '']);
        $this->mockArgv(['--opt']);

        $this->assertSame(
            ['opt' => false],
            $dummy->parseArguments()
        );
    }

    private function mockArgv(array $args) {
        global $argv;

        $merged = ['script'];

        foreach ($args as $arg)
            $merged[] = $arg;

        $argv = $merged;
    }
}