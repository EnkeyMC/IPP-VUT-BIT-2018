<?php

use PHPUnit\Framework\TestCase;

class ParserAppTest extends TestCase
{
    const FILES = [
        'input1' => 'output1.xml',
        'input2' => 'output2.xml',
        'input3' => 'output3.xml',
        'input4' => 'output4.xml',
        'input5' => 'output5.xml',
        'input6' => 'output6.xml',
        'input7' => 'output7.xml',
        'input8' => 'output8.xml',
    ];

    private $parser;
    private $stream;

    public function setUp() {
        global $argv;
        $argv = [];
        $this->parser = ParserApp::getInstance();
        $this->setParserProperty('xmlOutput', new XMLOutput());
    }

    public function tearDown()
    {
        @fclose($this->stream);
    }

    private function setInputFile($filename) {
        $this->stream = fopen($filename, 'r');
        $this->setParserProperty('codeAnalyzer', new CodeAnalyzer(new IPPcode18(), $this->stream));
    }

    private function invokeParse() {
        $reflection = new ReflectionClass(ParserApp::class);
        $method = $reflection->getMethod('parse');
        $method->setAccessible(true);
        return $method->invokeArgs($this->parser, []);
    }

    private function setParserProperty($property, $value) {
        $reflection = new ReflectionClass(ParserApp::class);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);

        $reflection_property->setValue($this->parser, $value);
    }

    private function getParserProperty($property) {
        $reflection = new ReflectionClass(ParserApp::class);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);

        return $reflection_property->getValue($this->parser);
    }

    public function testXML() {
        foreach (self::FILES as $input => $output) {
            $this->setUp();
            $this->setInputFile('unittests/files/'.$input);

            $this->invokeParse();
            $this->assertXmlStringEqualsXmlFile('unittests/files/'.$output, $this->getParserProperty('xmlOutput')->getOutput(), $input);
            $this->tearDown();
        }
    }
}