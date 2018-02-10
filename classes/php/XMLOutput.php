<?php

/**
 * Class XMLOutput
 *
 * Handles parsers XML Output
 */
final class XMLOutput
{
    const XML_VERSION = '1.0';
    const XML_ENCODING = 'UTF-8';
    const LANGUAGE = 'IPPcode18';

    const EL_PROGRAM = 'program';
    const EL_INSTRUCTION = 'instruction';
    const EL_ARG = 'arg';

    const ATTR_LANGUAGE = 'language';
    const ATTR_ORDER = 'order';
    const ATTR_OPCODE = 'opcode';
    const ATTR_TYPE = 'type';

    /**
     * @var XMLWriter
     */
    private $xw;

    /**
     * @var int current instruction order
     */
    private $instructionOrder;

    public function __construct(XMLWriter $writer = NULL)
    {
        if ($writer === NULL)
            $this->xw = new XMLWriter();
        else
            $this->xw = $writer;

        $this->instructionOrder = 1;
        $this->xw->openMemory();
        $this->xw->startDocument(self::XML_VERSION, self::XML_ENCODING);
        $this->xw->setIndent(true);
        $this->xw->startElement(self::EL_PROGRAM);
        $this->addAttribute(self::ATTR_LANGUAGE, self::LANGUAGE);
    }

    public function endOutput() {
        $this->xw->endElement();
        $this->xw->endDocument();
    }

    public function getOutput() {
        return $this->xw->outputMemory();
    }

    public function startInstruction($opcode) {
        $this->xw->startElement(self::EL_INSTRUCTION);
        $this->addAttribute(self::ATTR_ORDER, $this->instructionOrder);
        $this->addAttribute(self::ATTR_OPCODE, $opcode);

        $this->instructionOrder++;
    }

    public function endInstruction() {
        $this->xw->endElement();
    }

    public function addArgument($n, $type, $value) {
        $this->xw->startElement(self::EL_ARG . $n);
        $this->addAttribute(self::ATTR_TYPE, $type);
        $this->xw->text($value);
        $this->xw->endElement();
    }

    private function addAttribute($attribute, $text) {
        $this->xw->startAttribute($attribute);
        $this->xw->text($text);
        $this->xw->endAttribute();
    }
}