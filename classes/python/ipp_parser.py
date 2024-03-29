from classes.python.exceptions import XMLFormatError, SrcSyntaxError, LexicalError, ApplicationError
from classes.python.exit_codes import INPUT_FILE_ERROR
from classes.python.instruction import Instruction
from xml.etree import ElementTree
import re

__author__ = "Martin Omacht"
__copyright__ = "Copyright 2018"
__credits__ = ["Martin Omacht"]


class IPPParser:
    """
    Parse XML IPPcode18 representation to element tree and check it's validity
    """

    def __init__(self):
        """
        Initialize parser
        """
        self.arg_regex = re.compile(r"^arg(\d+)$")

    def parse_from_file(self, file: str) -> ElementTree.Element:
        """
        Parse XML from given file
        :param file: file with xml
        :return: root XML DOM element
        """
        try:
            xml_dom = ElementTree.parse(file).getroot()
        except ElementTree.ParseError:
            raise XMLFormatError("Vstupní XML nemá správný formát'")
        except FileNotFoundError:
            raise ApplicationError("Nepodařilo se otevřít vstupní soubor '{}'".format(file), INPUT_FILE_ERROR)

        self._check_xml_structure(xml_dom)
        return xml_dom

    def parse_from_string(self, xml_string: str) -> ElementTree.Element:
        """
        Parse XML from given string
        :param xml_string: string with xml
        :return: root XML DOM element
        """
        try:
            xml_dom = ElementTree.fromstring(xml_string)
        except ElementTree.ParseError:
            raise XMLFormatError("Vstupní XML nemá správný formát'")
        self._check_xml_structure(xml_dom)
        return xml_dom

    def _check_xml_structure(self, xml_dom: ElementTree.Element):
        """
        Check if XML structure is valid
        :param xml_dom: root XML DOM element
        """
        self._check_root_elem(xml_dom)
        self._check_instructions(xml_dom)

    def _check_root_elem(self, root_elem: ElementTree.Element):
        """
        Check root element
        :param root_elem: XML DOM root element
        """
        if root_elem.tag != "program":
            raise XMLFormatError("Vstupní XML musí mít kořenový element <program>")

        has_language = False
        for attrib, value in root_elem.attrib.items():
            if attrib == 'language':
                if value != 'IPPcode18':
                    raise XMLFormatError("Kořenový element <program> musí obsahovat atribut 'language=\"IPPcode18\"'")
                else:
                    has_language = True
            elif attrib not in ['name', 'description']:
                raise XMLFormatError("Nepovolený atribut '{}' elementu '<program>".format(attrib))

        if not has_language:
            raise XMLFormatError("Kořenový element <program> musí obsahovat atribut 'language=\"IPPcode18\"'")

    def _check_instructions(self, root_elem: ElementTree.Element):
        """
        Check instructions
        :param root_elem: XML DOM root element
        """
        for instruction in root_elem:
            if instruction.tag != "instruction":
                raise XMLFormatError("Neočekávaný element {}".format(instruction.tag))

            self._check_instruction_attribs(instruction)

            self._check_instruction_args(instruction)

    def _check_instruction_attribs(self, instruction: ElementTree.Element):
        """
        Check instruction attributes
        :param instruction: XML DOM instruction element
        """
        has_opcode = has_order = False
        for attrib, value in instruction.attrib.items():
            if attrib == "order":
                try:
                    int(value)
                    has_order = True
                except ValueError:
                    raise LexicalError("Atribut 'order' neobsahuje číselnou hodnotu")
            elif attrib == "opcode":
                if value not in Instruction.INSTRUCTION_ARGS:
                    raise SrcSyntaxError("Neplatný operační kód '{}'".format(value))
                else:
                    has_opcode = True
            else:
                raise XMLFormatError("Neznámý atribut '{}'".format(attrib))

        if not has_order or not has_opcode:
            raise XMLFormatError("Chybí atribut 'order' nebo 'opcode' v elementu <instruction>")

    def _check_instruction_args(self, instruction: ElementTree.Element):
        """
        Check instruction arguments
        :param instruction: XML DOM instruction element
        """
        args = list()
        opcode = instruction.attrib['opcode']
        order = instruction.attrib['order']
        for arg in instruction:
            matches = self.arg_regex.match(arg.tag)
            if matches:
                nth = int(matches.group(1))
                if nth in args:
                    raise XMLFormatError("Duplikátní argument {} instrukce {}".format(arg.tag, order))
                args.append(nth)
                Instruction.is_valid_arg(opcode, nth, arg)
            else:
                raise XMLFormatError("Neplatný agrument {} instrukce {}".format(arg.tag, order))

        if len(args) != Instruction.get_opcode_arg_num(opcode):
            raise SrcSyntaxError("Neplatný počet argumentů instrukce {}".format(order))

        for i in range(1, len(args) + 1):
            if i not in args:
                raise XMLFormatError("Chybí argument 'arg{}' v instrukci {}".format(i, order))
