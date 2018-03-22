from unittest.case import TestCase

from classes.python.ipp_parser import IPPParser
from classes.python.program import Program
from classes.python.exceptions import *


class TestProgram(TestCase):

    def setUp(self):
        self.parser = IPPParser()

    def test_aritmetic_simple(self):
        xml_dom = self.parser.parse_from_file('../files/output1.xml')
        program = Program(xml_dom)
        program.analyze()

    def test_label_redefinition(self):
        xml_dom = self.parser.parse_from_string(
            """<?xml version="1.0" encoding="UTF-8"?>
            <program language="IPPcode18">
                <instruction order="1" opcode="LABEL">
                    <arg1 type="label">lab</arg1>
                </instruction>
                <instruction order="2" opcode="LABEL">
                    <arg1 type="label">lab</arg1>
                </instruction>
            </program>"""
        )

        program = Program(xml_dom)
        self.assertRaises(
            SemanticError,
            program.analyze
        )

    def test_jump_to_invalid_label(self):
        xml_dom = self.parser.parse_from_string(
            """<?xml version="1.0" encoding="UTF-8"?>
            <program language="IPPcode18">
                <instruction order="1" opcode="LABEL">
                    <arg1 type="label">lab</arg1>
                </instruction>
                <instruction order="2" opcode="JUMP">
                    <arg1 type="label">invalid</arg1>
                </instruction>
            </program>"""
        )

        program = Program(xml_dom)
        program.analyze()
        self.assertRaises(
            SemanticError,
            program.interpret()
        )

    def test_call_invalid_label(self):
        xml_dom = self.parser.parse_from_string(
            """<?xml version="1.0" encoding="UTF-8"?>
            <program language="IPPcode18">
                <instruction order="1" opcode="LABEL">
                    <arg1 type="label">lab</arg1>
                </instruction>
                <instruction order="2" opcode="CALL">
                    <arg1 type="label">invalid</arg1>
                </instruction>
            </program>"""
        )

        program = Program(xml_dom)
        program.analyze()
        self.assertRaises(
            SemanticError,
            program.interpret()
        )

