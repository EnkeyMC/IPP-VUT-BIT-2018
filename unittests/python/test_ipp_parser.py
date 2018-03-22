from unittest.case import TestCase

from classes.python.ipp_parser import IPPParser
from classes.python.exceptions import XMLFormatError, SrcSyntaxError, LexicalError, SemanticError


class TestIPPParser(TestCase):
    def setUp(self):
        self.parser = IPPParser()

    def test_empty(self):
        self.skipTest("for now")  # TODO don't skip later
        self.assertTrue(self.parser.parse_from_string(
            """<?xml version="1.0" encoding="UTF-8"?><program language="IPPcode18"></program>"""))

    def test_no_root_tag(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>"""
        )

    def test_no_lang_attrib(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?><program></program>"""
        )

    def test_name_attrib(self):
        try:
            self.parser.parse_from_string(
                """<?xml version="1.0" encoding="UTF-8" ?><program language="IPPcode18" name="test"></program>"""
            )
        except Exception as e:
            self.fail("Exception {} was thrown!".format(repr(e)))

    def test_description_attrib(self):
        try:
            self.parser.parse_from_string(
                """<?xml version="1.0" encoding="UTF-8" ?><program language="IPPcode18" description="test"></program>"""
            )
        except Exception as e:
            self.fail("Exception {} was thrown!".format(repr(e)))

    def test_invalid_program_attrib(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?><program language="IPPcode18" invalid="test"></program>"""
        )

    def test_invalid_inst_tag(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <invalid opcode="CREATEFRAME">
                </invalid>
            </program>"""
        )

    def test_order_nan(self):
        self.assertRaises(
            LexicalError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="f" opcode="CREATEFRAME">
                </instruction>
            </program>"""
        )

    def test_order_empty(self):
        self.assertRaises(
            LexicalError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="" opcode="CREATEFRAME">
                </instruction>
            </program>"""
        )

    def test_instruction_invalid_attr(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="CREATEFRAME" invalid="dfa">
                </instruction>
            </program>"""

        )

    def test_invalid_opcode(self):
        self.assertRaises(
            SrcSyntaxError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="INVALID">
                </instruction>
            </program>"""

        )

    def test_invalid_inst_arg(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="CREATEFRAME">
                    <invalid></invalid>
                </instruction>
            </program>"""
        )

    def test_multiple_opcodes(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="CREATEFRAME" opcode="POPFRAME">
                </instruction>
            </program>"""
        )

    def test_missing_opcode(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1">
                </instruction>
            </program>"""
        )

    def test_missing_order(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction opcode="CREATEFRAME">
                </instruction>
            </program>"""
        )

    def test_inst_skipped_arg(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="NOT">
                    <arg2 type="var">GF@var</arg2>
                </instruction>
            </program>"""
        )

    def test_duplicate_arg(self):
        self.assertRaises(
            XMLFormatError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="NOT">
                    <arg1 type="var">GF@var</arg1>
                    <arg1 type="var">GF@var</arg1>
                </instruction>
            </program>"""
        )

    def test_invalid_number_of_args(self):
        self.assertRaises(
            SemanticError,
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="NOT">
                    <arg1 type="var">GF@var</arg1>
                </instruction>
            </program>"""
        )

    def test_invalid_arg_type(self):
        self.assertRaises(
            SrcSyntaxError,  # TODO check if this is right
            self.parser.parse_from_string,
            """<?xml version="1.0" encoding="UTF-8" ?>
            <program language="IPPcode18">
                <instruction order="1" opcode="NOT">
                    <arg1 type="var">GF@var</arg1>
                    <arg2 type="label">label</arg2>
                </instruction>
            </program>"""
        )
