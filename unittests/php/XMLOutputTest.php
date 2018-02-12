<?php

use PHPUnit\Framework\TestCase;

final class XMLOutputTest extends TestCase
{
    const TEST_OPCODE = 'TEST';

    private $XMLWriterMock;

    public function setUp() {
        $this->XMLWriterMock = $this->getMockBuilder(XMLWriter::class)->getMock();
    }

    private function setupMockConstructorExpectations() {
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
    }

    public function testConstructor() {
        $this->setupMockConstructorExpectations();
        new XMLOutput($this->XMLWriterMock);
    }

    public function testEndOutput() {
        $this->XMLWriterMock->expects($this->once())->method('endElement');
        $this->XMLWriterMock->expects($this->once())->method('endDocument');

        $xo = new XMLOutput($this->XMLWriterMock);
        $xo->endOutput();
    }

    public function testGetOutput() {
        $this->XMLWriterMock->method('outputMemory')->willReturn('xml_output');

        $xo = new XMLOutput($this->XMLWriterMock);
        $this->assertSame('xml_output', $xo->getOutput());
    }

    public function testStartInstruction() {
        $this->XMLWriterMock->method('startElement')
                            ->withConsecutive([XMLOutput::EL_PROGRAM, XMLOutput::EL_INSTRUCTION);
        $this->XMLWriterMock->method('startAttribute')
                            ->withConsecutive([XMLOutput::ATTR_LANGUAGE, XMLOutput::ATTR_ORDER, XMLOutput::ATTR_OPCODE]);
        $this->XMLWriterMock->expects($this->ex())->method('text')
                            ->with(1);
        $this->XMLWriterMock->expects($this->once())->method('text')
                            ->with(self::TEST_OPCODE);
        $this->XMLWriterMock->expects($this->exactly(2))->method('endAttribute');

        $xo = new XMLOutput($this->XMLWriterMock);
        $xo->startInstruction(self::TEST_OPCODE);
    }

    public function testEndInstruction() {
        $this->XMLWriterMock->expects($this->once())->method('endElement');

        $xo = new XMLOutput($this->XMLWriterMock);
        $xo->endInstruction();
    }

    public function testAddArgument() {
        $this->XMLWriterMock->expects($this->once())->method('startElement')
            ->with(XMLOutput::EL_ARG . '1');
        $this->XMLWriterMock->expects($this->once())->method('text')
            ->with('test_text');
        $this->XMLWriterMock->expects($this->once())->method('endElement');

        $xo = new XMLOutput($this->XMLWriterMock);
        $xo->addArgument(1, 'label', 'test_text');
    }
}