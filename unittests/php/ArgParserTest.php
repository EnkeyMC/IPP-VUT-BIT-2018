<?php

use PHPUnit\Framework\TestCase;

class ArgParserTest extends TestCase {

    public function testNoOptions() {
        $dummy = new ArgParser([]);
        $dummy->mockGetoptResult = [];

        $this->assertSame([], $dummy->parseArguments());
    }

    public function testOnlyLongOptions() {
        $options = ['option' => '', 'option2' => ''];
        $dummy = new ArgParser($options);
        $dummy->mockGetoptResult = ['option' => false, 'option2' => false];

        $this->assertSame($dummy->mockGetoptResult, $dummy->parseArguments());
    }

    public function testShortAliases() {
        $options = ['testShort' => 'o', 'testLong' => 't'];
        $dummy = new ArgParser($options);
        $dummy->mockGetoptResult = ['o' => false, 'testLong' => false];

        $this->assertSame(
            ['testShort' => false, 'testLong' => false],
            $dummy->parseArguments()
        );
    }

    public function testShortAliasesWithValue() {
        $options = ['opt1:' => 'o:', 'opt2::' => 't::'];
        $dummy = new ArgParser($options);
        $dummy->mockGetoptResult = ['o' => 'value', 'opt2' => 'value'];

        $this->assertSame(
            ['opt1' => 'value', 'opt2' => 'value'],
            $dummy->parseArguments()
        );
    }
}