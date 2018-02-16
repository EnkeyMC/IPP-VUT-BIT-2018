<?php

use PHPUnit\Framework\TestCase;

final class XMLOutputTest extends TestCase
{
    const TEST_OPCODE = 'TEST';

    private $XMLWriterMock;
    private $dummy;

    public function setUp() {
        $this->XMLWriterMock = $this->getMockBuilder(XMLWriter::class)->getMock();
        $this->dummy = new XMLOutput($this->XMLWriterMock);
    }

    public function testStartOutput() {
        $this->XMLWriterMock->expects($this->once())->method('openMemory');
        $this->XMLWriterMock->expects($this->once())->method('startDocument')
                            ->with(XMLOutput::XML_VERSION, XMLOutput::XML_ENCODING);
        $this->XMLWriterMock->expects($this->once())->method('startElement')
                            ->with(XMLOutput::EL_PROGRAM);
        $this->XMLWriterMock->expects($this->once())->method('startAttribute')
                            ->with(XMLOutput::ATTR_LANGUAGE);
        $this->XMLWriterMock->expects($this->once())->method('text')
                            ->with(XMLOutput::LANGUAGE);
        $this->XMLWriterMock->expects($this->once())->method('endAttribute');

        $this->dummy->startOutput();
    }

    public function testEndOutput() {
        $this->XMLWriterMock->expects($this->once())->method('endElement');
        $this->XMLWriterMock->expects($this->once())->method('endDocument');

        $this->dummy->endOutput();
    }

    public function testGetOutput() {
        $this->XMLWriterMock->method('outputMemory')->willReturn('xml_output');

        $this->assertSame('xml_output', $this->dummy->getOutput());
    }

    public function testStartInstruction() {
        $this->XMLWriterMock->method('startElement')
                            ->withConsecutive(
                                [XMLOutput::EL_INSTRUCTION]);
        $this->XMLWriterMock->method('startAttribute')
                            ->withConsecutive(
                                [XMLOutput::ATTR_ORDER],
                                [XMLOutput::ATTR_OPCODE]);
        $this->XMLWriterMock->method('text')
                            ->withConsecutive(
                                [1],
                                [self::TEST_OPCODE]);
        $this->XMLWriterMock->expects($this->exactly(2))->method('endAttribute');

        $this->dummy->startInstruction(self::TEST_OPCODE);
    }

    public function testEndInstruction() {
        $this->XMLWriterMock->expects($this->once())->method('endElement');

        $this->dummy->endInstruction();
    }

    public function testAddArgument() {
        $this->XMLWriterMock->method('startElement')
                            ->withConsecutive(
                                [XMLOutput::EL_ARG . '1']);
        $this->XMLWriterMock->method('text')
                            ->withConsecutive(
                                ['label'],
                                ['test_text']);
        $this->XMLWriterMock->expects($this->once())->method('endElement');

        $this->dummy->addArgument(1, 'label', 'test_text');
    }

    public function testXMLOutput() {
        $expectedXML = '<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="DEFVAR">
                    <arg1 type="var">GF@var</arg1>
                </instruction>
                <instruction order="2" opcode="MOVE">
                    <arg1 type="var">GF@var</arg1>
                    <arg2 type="var">GF@var</arg2>
                </instruction>
             </program>';

        $xo = new XMLOutput();
        $xo->startOutput();
        $xo->startInstruction('DEFVAR');
        $xo->addArgument(1, 'var', 'GF@var');
        $xo->endInstruction();
        $xo->startInstruction('MOVE');
        $xo->addArgument(1, 'var', 'GF@var');
        $xo->addArgument(2, 'var', 'GF@var');
        $xo->endInstruction();
        $xo->endOutput();

        $this->assertXmlStringEqualsXmlString($expectedXML, $xo->getOutput());
    }
}