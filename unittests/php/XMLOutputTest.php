<?php

use PHPUnit\Framework\TestCase;

final class XMLOutputTest extends TestCase
{
    private $XMLWriterMock;

    public function setUp() {
        $this->XMLWriterMock = $this->getMockBuilder(XMLWriter::class)->getMock();
    }

    public function testConstructor() {
        $this->XMLWriterMock->expects($this->once())->method('openMemory');
        $this->XMLWriterMock->expects($this->once())->method('startDocument')->with(XMLOutput::XML_VERSION, XMLOutput::XML_ENCODING);
        $this->XMLWriterMock->expects($this->once())->method('startElement')->with(XMLOutput::EL_PROGRAM);
        $this->XMLWriterMock->expects($this->once())->method('startAttribute')->with(XMLOutput::ATTR_LANGUAGE);
        $this->XMLWriterMock->expects($this->once())->method('text')->with(XMLOutput::LANGUAGE);
        $this->XMLWriterMock->expects($this->once())->method('endAttribute');

        new XMLOutput($this->XMLWriterMock);
    }
}