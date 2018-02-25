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

    /**
     * XMLOutput constructor.
     * @param XMLWriter|NULL $writer instance of XMLWriter or null to create new one
     */
    public function __construct(XMLWriter $writer = null)
    {
        if ($writer === null)
            $this->xw = new XMLWriter();
        else
            $this->xw = $writer;

        $this->instructionOrder = 1;
    }

    /**
     * Initialize output
     *
     * Must to be called before adding anything to output
     *
     * endOutput() has to be called after output is finished
     */
    public function startOutput() {
        $this->xw->openMemory();
        $this->xw->startDocument(self::XML_VERSION, self::XML_ENCODING);
        $this->xw->setIndent(true);
        $this->xw->startElement(self::EL_PROGRAM);
        $this->addAttribute(self::ATTR_LANGUAGE, self::LANGUAGE);
    }

    /**
     * End xml output
     *
     * Must be called before getOutput()
     */
    public function endOutput() {
        $this->xw->endElement();
        $this->xw->endDocument();
    }

    /**
     * Get XML output string
     *
     * @return string XML
     */
    public function getOutput() {
        return $this->xw->outputMemory();
    }

    /**
     * Start instruction element with given opcode
     *
     * @param $opcode string
     */
    public function startInstruction($opcode) {
        $this->xw->startElement(self::EL_INSTRUCTION);
        $this->addAttribute(self::ATTR_ORDER, $this->instructionOrder);
        $this->addAttribute(self::ATTR_OPCODE, $opcode);

        $this->instructionOrder++;
    }

    /**
     * End current instruction
     *
     * startInstruction() must be called before
     */
    public function endInstruction() {
        $this->xw->endElement();
    }

    /**
     * Add argument to current instruction
     *
     * @param $n int argument order
     * @param $type string argument type
     * @param $value string argument value
     */
    public function addArgument($n, $type, $value) {
        $this->xw->startElement(self::EL_ARG . $n);
        $this->addAttribute(self::ATTR_TYPE, $type);
        $this->xw->text($value);
        $this->xw->endElement();
    }

    /**
     * Add attribute to element
     *
     * @param $attribute string
     * @param $text string value
     */
    private function addAttribute($attribute, $text) {
        $this->xw->startAttribute($attribute);
        $this->xw->text($text);
        $this->xw->endAttribute();
    }
}